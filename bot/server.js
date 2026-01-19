const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const db = require('./db');
const qrcode = require('qrcode-terminal');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

const clients = {}; // Map: instanceId -> Client

// Initialize existing connected instances on startup
async function restoreSessions() {
    try {
        const [rows] = await db.query("SELECT * FROM instances WHERE status = 'connected'");
        for (const instance of rows) {
            console.log(`Restoring session for instance ${instance.id}`);
            startSession(instance.id, instance.session_name, false);
        }
    } catch (err) {
        console.error("Error restoring sessions:", err);
    }
}

function startSession(instanceId, sessionName, isNew = false, socket = null) {
    if (clients[instanceId]) {
        if (socket) socket.emit('log', 'Session already active');
        return;
    }

    const client = new Client({
        authStrategy: new LocalAuth({ clientId: sessionName }),
        puppeteer: {
            headless: true,
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        }
    });

    client.on('qr', (qr) => {
        console.log('QR RECEIVED', qr);
        if (socket) {
            socket.emit('qr', qr);
        }
        // Update status to disconnected until scanned
        db.query("UPDATE instances SET status = 'disconnected' WHERE id = ?", [instanceId]);
    });

    client.on('ready', async () => {
        console.log('Client is ready!');
        const phone = client.info.wid.user;

        // Check for duplicates
        const [rows] = await db.query("SELECT id FROM instances WHERE phone = ? AND id != ?", [phone, instanceId]);
        if (rows.length > 0) {
            console.log(`Duplicate phone ${phone} detected. Logging out.`);
            if (socket) socket.emit('auth_failure', 'This phone number is already linked to another instance.');
            await client.logout();
            await client.destroy();
            delete clients[instanceId];
            return;
        }

        if (socket) socket.emit('ready', 'Client is ready');
        db.query("UPDATE instances SET status = 'connected', phone = ? WHERE id = ?", [phone, instanceId]);
    });

    client.on('authenticated', () => {
        console.log('AUTHENTICATED');
        if (socket) socket.emit('authenticated', 'Authenticated');
    });

    client.on('auth_failure', msg => {
        console.error('AUTHENTICATION FAILURE', msg);
        if (socket) socket.emit('auth_failure', msg);
    });

    client.on('disconnected', (reason) => {
        console.log('Client was logged out', reason);
        if (socket) socket.emit('disconnected', reason);
        db.query("UPDATE instances SET status = 'disconnected' WHERE id = ?", [instanceId]);
        delete clients[instanceId];
        client.destroy();
    });

    client.on('message', async (msg) => {
        console.log(`Incoming message from ${msg.from}: ${msg.body}`);

        if (msg.from.endsWith('@c.us')) {
            const phone = msg.from.replace('@c.us', '').replace(/\D/g, '');
            try {
                const [inst] = await db.query("SELECT user_id FROM instances WHERE id = ?", [instanceId]);
                const userId = inst.length > 0 ? inst[0].user_id : null;

                let mediaPath = null;
                if (msg.hasMedia) {
                    try {
                        const media = await msg.downloadMedia();
                        if (media) {
                            const fs = require('fs');
                            const path = require('path');
                            const filename = `${Date.now()}_${Math.random().toString(36).substring(7)}.${media.mimetype.split('/')[1]}`;
                            const fullPath = path.join('/var/www/html/wwebjs/public/uploads/inbound', filename);
                            fs.writeFileSync(fullPath, media.data, { encoding: 'base64' });
                            mediaPath = `/uploads/inbound/${filename}`;
                        }
                    } catch (mediaErr) {
                        console.error("Error downloading inbound media:", mediaErr);
                    }
                }

                await db.query(
                    "INSERT INTO messages (user_id, instance_id, phone, body, status, direction, message_id, media_path, created_at, sent_at) VALUES (?, ?, ?, ?, 'sent', 'inbound', ?, ?, NOW(), NOW())",
                    [userId, instanceId, phone, msg.body || '', msg.id._serialized, mediaPath]
                );
            } catch (err) {
                console.error("Error saving inbound message:", err);
            }
        }
    });

    client.initialize();
    clients[instanceId] = client;
}

io.on('connection', (socket) => {
    console.log('New client connected');

    socket.on('start_session', async (data) => {
        const { instanceId, sessionName } = data;
        console.log(`Starting session for ${instanceId}`);
        startSession(instanceId, sessionName, true, socket);
    });

    socket.on('disconnect', () => {
        console.log('Client disconnected');
    });
});

app.use(express.json());
app.use(express.urlencoded({ extended: true }));

app.post('/logout', async (req, res) => {
    const { instanceId } = req.body;
    console.log(`Logout requested for instance ${instanceId}`);

    if (clients[instanceId]) {
        try {
            await clients[instanceId].logout();
            await clients[instanceId].destroy();
            delete clients[instanceId];
        } catch (e) {
            console.error('Error during logout:', e);
        }
    }

    await db.query("UPDATE instances SET status = 'disconnected', phone = NULL WHERE id = ?", [instanceId]);
    res.json({ success: true });
});

// Queue Worker Logic
const RATE_LIMIT_DELAY = 10000; // 10 seconds between messages per instance (simple rate limit)
const lastSentTime = {}; // instanceId -> timestamp

async function processQueue() {
    try {
        // First, move scheduled messages to pending when their time arrives
        await db.query(`
            UPDATE messages 
            SET status = 'pending', is_scheduled = 0 
            WHERE status = 'scheduled' 
            AND scheduled_at <= NOW()
        `);

        const [messages] = await db.query("SELECT * FROM messages WHERE status = 'pending' ORDER BY created_at ASC LIMIT 10");

        for (const msg of messages) {
            const client = clients[msg.instance_id];

            if (!client) {
                console.log(`Client not active for instance ${msg.instance_id}`);
                continue;
            }

            const now = Date.now();
            const lastSent = lastSentTime[msg.instance_id] || 0;

            if (now - lastSent < RATE_LIMIT_DELAY) {
                continue; // Skip this instance for now
            }

            try {
                // Format phone number (remove +, spaces, etc. and append @c.us)
                let chatId = msg.phone.replace(/\D/g, '');
                if (!chatId.endsWith('@c.us')) {
                    chatId += '@c.us';
                }

                let sentMsg;
                const options = { sendSeen: false };
                if (msg.reply_to_id) {
                    options.quotedMessageId = msg.reply_to_id;
                }

                if (msg.media_path) {
                    try {
                        const media = MessageMedia.fromFilePath('/var/www/html/wwebjs/public' + msg.media_path);
                        sentMsg = await client.sendMessage(chatId, media, { ...options, caption: msg.body });
                    } catch (mediaError) {
                        console.error('Error creating/sending media:', mediaError);
                        // Fallback to text only if media fails
                        sentMsg = await client.sendMessage(chatId, msg.body + "\n[Image Failed]", options);
                    }
                } else {
                    sentMsg = await client.sendMessage(chatId, msg.body, options);
                }

                await db.query("UPDATE messages SET status = 'sent', sent_at = NOW(), message_id = ? WHERE id = ?", [sentMsg.id._serialized, msg.id]);
                lastSentTime[msg.instance_id] = now;
                console.log(`Message ${msg.id} sent`);
            } catch (err) {
                console.error(`Failed to send message ${msg.id}:`, err);
                await db.query("UPDATE messages SET status = 'failed' WHERE id = ?", [msg.id]);
            }
        }
    } catch (err) {
        console.error("Queue processing error:", err);
    }
}

setInterval(processQueue, 5000); // Check queue every 5 seconds

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
    restoreSessions();
});
