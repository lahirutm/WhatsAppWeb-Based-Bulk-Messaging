<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Chat - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            background-color: #f0f2f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .chat-container {
            display: flex;
            height: calc(100vh - 56px);
            overflow: hidden;
            background-color: #fff;
        }

        .chat-sidebar {
            width: 30%;
            min-width: 300px;
            max-width: 450px;
            background: white;
            border-right: 1px solid #d1d7db;
            display: flex;
            flex-direction: column;
        }

        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #efeae2 url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
            position: relative;
        }

        .sidebar-header {
            padding: 10px 16px;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-box {
            padding: 7px 10px;
            background: #fff;
            border-bottom: 1px solid #e9edef;
        }

        .search-box .input-group {
            background: #f0f2f5;
            border-radius: 8px;
            padding: 0 12px;
        }

        .search-box input {
            background: transparent !important;
            font-size: 14px;
            height: 35px;
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #f0f2f5;
            transition: background 0.2s;
        }

        .chat-item:hover {
            background-color: #f5f6f6;
        }

        .chat-item.active {
            background-color: #f0f2f5;
        }

        .chat-avatar {
            width: 49px;
            height: 49px;
            border-radius: 50%;
            background: #dfe5e7;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
            color: #919191;
            flex-shrink: 0;
        }

        .chat-info {
            flex: 1;
            min-width: 0;
        }

        .chat-name {
            font-size: 16px;
            font-weight: 400;
            color: #111b21;
            margin-bottom: 2px;
        }

        .chat-last-msg {
            font-size: 13px;
            color: #667781;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chat-time {
            font-size: 12px;
            color: #667781;
        }

        .chat-header {
            padding: 10px 16px;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #d1d7db;
            z-index: 10;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 7%;
            display: flex;
            flex-direction: column;
        }

        .message {
            max-width: 65%;
            padding: 6px 7px 8px 9px;
            border-radius: 7.5px;
            margin-bottom: 2px;
            position: relative;
            font-size: 14.2px;
            line-height: 19px;
            color: #111b21;
            box-shadow: 0 1px 0.5px rgba(11, 20, 26, .13);
        }

        .message.sent {
            align-self: flex-end;
            background-color: #d9fdd3;
            border-top-right-radius: 0;
            margin-bottom: 8px;
        }

        .message.received {
            align-self: flex-start;
            background-color: #fff;
            border-top-left-radius: 0;
            margin-bottom: 8px;
        }

        .message-time {
            font-size: 11px;
            color: #667781;
            text-align: right;
            margin-top: 4px;
            margin-left: 10px;
            display: inline-block;
        }

        .chat-input-area {
            padding: 5px 16px;
            background: #f0f2f5;
            display: flex;
            align-items: center;
            min-height: 62px;
        }

        .chat-input {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 9px 12px;
            margin: 5px 10px;
            outline: none;
            background: #fff;
            font-size: 15px;
        }

        .btn-icon {
            background: none;
            border: none;
            font-size: 24px;
            color: #54656f;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .btn-icon:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #667781;
            text-align: center;
            padding: 0 50px;
        }

        .empty-chat i {
            font-size: 100px;
            margin-bottom: 20px;
            color: #cbd5de;
        }

        .empty-chat h3 {
            color: #41525d;
            font-weight: 300;
            font-size: 32px;
        }

        .empty-chat p {
            font-size: 14px;
            line-height: 20px;
        }

        /* Emoji Picker Styling */
        emoji-picker {
            position: absolute;
            bottom: 70px;
            left: 16px;
            z-index: 100;
            display: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .emoji-active emoji-picker {
            display: block;
        }

        .preview-container {
            padding: 10px 16px;
            background: #f0f2f5;
            border-top: 1px solid #d1d7db;
            display: none;
            align-items: center;
            gap: 15px;
        }

        .preview-image-wrapper {
            position: relative;
            width: 60px;
            height: 60px;
        }

        .preview-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #d1d7db;
        }

        .remove-preview {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #54656f;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            border: 2px solid #f0f2f5;
        }

        .remove-preview:hover {
            background: #111b21;
        }

        /* Message Actions */
        .message-actions {
            position: absolute;
            top: 5px;
            right: 5px;
            display: none;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            z-index: 5;
        }

        .message:hover .message-actions {
            display: flex;
        }

        .action-btn {
            padding: 2px 6px;
            cursor: pointer;
            color: #54656f;
            font-size: 14px;
        }

        .action-btn:hover {
            color: #111b21;
        }

        /* Reply Preview */
        .reply-container {
            padding: 10px 16px;
            background: #f0f2f5;
            border-top: 1px solid #d1d7db;
            display: none;
            flex-direction: column;
            position: relative;
        }

        .reply-content-preview {
            background: rgba(0, 0, 0, 0.05);
            border-left: 4px solid #06d755;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
        }

        .reply-close {
            position: absolute;
            top: 5px;
            right: 16px;
            cursor: pointer;
            color: #54656f;
        }

        /* Replied Message in Bubble */
        .replied-message {
            background: rgba(0, 0, 0, 0.05);
            border-left: 4px solid #06d755;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 5px;
            color: #54656f;
        }

        .replied-message.sent {
            border-left-color: #06d755;
        }

        .replied-message.received {
            border-left-color: #34b7f1;
        }
    </style>
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>
</head>

<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="chat-avatar" style="width: 40px; height: 40px;"><i class="bi bi-person-circle"></i></div>
                <div class="d-flex">
                    <button class="btn-icon"><i class="bi bi-people-fill"></i></button>
                    <button class="btn-icon"><i class="bi bi-circle-uppercase"></i></button>
                    <button class="btn-icon" id="newChatBtn" title="New Chat"><i
                            class="bi bi-chat-left-text-fill"></i></button>
                    <button class="btn-icon"><i class="bi bi-three-dots-vertical"></i></button>
                </div>
            </div>
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0"><i
                            class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control border-0 shadow-none" placeholder="Search or start new chat"
                        id="chatSearch">
                </div>
            </div>
            <div class="chat-list" id="chatList">
                <!-- Chat items will be loaded here -->
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main" id="chatMain">
            <div class="empty-chat" id="emptyChat">
                <i class="bi bi-laptop"></i>
                <h3>WhatsApp Web</h3>
                <p>Send and receive messages without keeping your phone online.<br>Use WhatsApp on up to 4 linked
                    devices and 1 phone at the same time.</p>
                <div class="mt-auto mb-4 text-muted" style="font-size: 12px;"><i class="bi bi-lock-fill"></i> End-to-end
                    encrypted</div>
            </div>

            <div id="activeChat" style="display: none; flex-direction: column; height: 100%;">
                <div id="connectionWarning" class="alert alert-warning m-0 border-0 rounded-0 py-2 text-center"
                    style="display: none; font-size: 14px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    WhatsApp is not connected. <a href="/dashboard" class="alert-link">Connect now</a> to send messages.
                </div>
                <div class="chat-header">
                    <div class="chat-avatar" style="width: 40px; height: 40px; font-size: 20px;"><i
                            class="bi bi-person-circle"></i></div>
                    <div class="chat-info">
                        <div class="chat-name" id="activeChatName">Phone Number</div>
                        <div class="chat-last-msg" style="font-size: 11px;">online</div>
                    </div>
                    <div class="d-flex">
                        <button class="btn-icon"><i class="bi bi-search"></i></button>
                        <button class="btn-icon"><i class="bi bi-three-dots-vertical"></i></button>
                    </div>
                </div>

                <div class="chat-messages" id="messageArea">
                    <!-- Messages will be loaded here -->
                </div>

                <div class="preview-container" id="previewContainer">
                    <div class="preview-image-wrapper">
                        <img src="" id="imagePreview" class="preview-image">
                        <div class="remove-preview" id="removePreview"><i class="bi bi-x"></i></div>
                    </div>
                    <div class="text-muted small" id="imageName">image.png</div>
                </div>

                <div class="reply-container" id="replyContainer">
                    <div class="reply-close" id="closeReply"><i class="bi bi-x-lg"></i></div>
                    <div class="reply-content-preview">
                        <div class="fw-bold text-success small" id="replySender">Sender</div>
                        <div class="text-muted text-truncate" id="replyText">Message content...</div>
                    </div>
                </div>

                <div class="chat-input-area">
                    <button class="btn-icon" id="emojiBtn"><i class="bi bi-emoji-smile"></i></button>
                    <emoji-picker id="emojiPicker"></emoji-picker>

                    <button class="btn-icon" onclick="document.getElementById('imageInput').click()"><i
                            class="bi bi-plus-lg"></i></button>
                    <input type="file" id="imageInput" style="display: none;" accept="image/*">

                    <input type="text" class="chat-input" placeholder="Type a message" id="messageInput"
                        autocomplete="off">
                    <button class="btn-icon" id="sendBtn"><i class="bi bi-send-fill text-primary"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Chat Modal -->
    <div class="modal fade" id="newChatModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Chat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="newChatPhone" placeholder="e.g. 94712345678">
                        <div class="form-text">Enter the phone number with country code.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="startChatBtn">Start Chat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forward Modal -->
    <div class="modal fade" id="forwardModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Forward message to</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="search-box">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0"><i
                                    class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control border-0 shadow-none" placeholder="Search contacts"
                                id="forwardSearch">
                        </div>
                    </div>
                    <div class="chat-list" id="forwardChatList" style="max-height: 400px;">
                        <!-- Recent chats will be loaded here for forwarding -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmForward" disabled>Forward</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const instanceId = <?php echo $_GET['instance_id']; ?>;
        let activePhone = null;
        let lastMessagesJson = '';
        let isFetchingHistory = false;

        async function loadRecentChats() {
            try {
                const response = await fetch(`/chat/recent?instance_id=${instanceId}`);
                if (!response.ok) throw new Error("Server error");
                const data = await response.json();
                const chats = data.chats;
                const instanceStatus = data.instance_status;

                // Handle connection warning
                const warning = document.getElementById('connectionWarning');
                const messageInput = document.getElementById('messageInput');
                const sendBtn = document.getElementById('sendBtn');

                if (instanceStatus !== 'connected') {
                    warning.style.display = 'block';
                    messageInput.disabled = true;
                    messageInput.placeholder = 'WhatsApp disconnected';
                    sendBtn.disabled = true;
                } else {
                    warning.style.display = 'none';
                    messageInput.disabled = false;
                    messageInput.placeholder = 'Type a message';
                    sendBtn.disabled = false;
                }

                const chatList = document.getElementById('chatList');

                // Only update if content changed (simple check)
                const currentContent = chatList.innerHTML;
                let newContent = '';

                chats.forEach(chat => {
                    newContent += `
                        <div class="chat-item ${activePhone === chat.phone ? 'active' : ''}" onclick="selectChat('${chat.phone}')">
                            <div class="chat-avatar"><i class="bi bi-person-circle"></i></div>
                            <div class="chat-info">
                                <div class="d-flex justify-content-between">
                                    <div class="chat-name">${chat.phone}</div>
                                    <div class="chat-time">${formatTime(chat.created_at)}</div>
                                </div>
                                <div class="chat-last-msg">
                                    ${chat.direction === 'inbound' ? '' : '<i class="bi bi-check2-all text-primary"></i> '}
                                    ${chat.last_message}
                                </div>
                            </div>
                        </div>
                    `;
                });

                if (newContent !== currentContent) {
                    chatList.innerHTML = newContent;
                }
            } catch (e) {
                console.error("Error loading chats:", e);
            }
        }

        function formatTime(dateTimeStr) {
            const date = new Date(dateTimeStr);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        async function selectChat(phone) {
            if (activePhone === phone) return;
            activePhone = phone;
            document.getElementById('emptyChat').style.display = 'none';
            document.getElementById('activeChat').style.display = 'flex';
            document.getElementById('activeChatName').innerText = phone;

            // Show loading state
            const messageArea = document.getElementById('messageArea');
            messageArea.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"></div></div>';

            // Clear any pending image when switching chats
            document.getElementById('imageInput').value = '';
            previewContainer.style.display = 'none';
            cancelReply();

            // Update active state in sidebar
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.toggle('active', item.querySelector('.chat-name').innerText === phone);
            });

            lastMessagesJson = ''; // Reset to force reload
            loadChatHistory();
        }

        async function loadChatHistory() {
            const phoneAtStart = activePhone;
            if (!phoneAtStart || isFetchingHistory) return;

            isFetchingHistory = true;
            try {
                const response = await fetch(`/chat/history?instance_id=${instanceId}&phone=${phoneAtStart}`);
                if (!response.ok) throw new Error("Server error");
                const messages = await response.json();

                // If user switched chats while fetching, ignore this response
                if (activePhone !== phoneAtStart) return;

                const currentJson = JSON.stringify(messages);
                if (currentJson === lastMessagesJson) return;
                lastMessagesJson = currentJson;

                const messageArea = document.getElementById('messageArea');
                messageArea.innerHTML = '';

                messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = `message ${msg.direction === 'inbound' ? 'received' : 'sent'}`;
                    div.dataset.id = msg.message_id;

                    let content = '';

                    // Render replied message if exists
                    if (msg.reply_to_id && msg.reply_body) {
                        content += `
                            <div class="replied-message ${msg.reply_direction}">
                                <div class="fw-bold small">${msg.reply_direction === 'inbound' ? activePhone : 'You'}</div>
                                <div class="text-truncate">${msg.reply_body}</div>
                            </div>
                        `;
                    }

                    if (msg.media_path) {
                        content += `<img src="${msg.media_path}" style="max-width: 100%; border-radius: 5px; margin-bottom: 5px; cursor: pointer;" onclick="window.open('${msg.media_path}')"><br>`;
                    }
                    content += msg.body.replace(/\n/g, '<br>');

                    div.innerHTML = `
                        <div class="message-actions">
                            <div class="action-btn" onclick="prepareReply('${msg.message_id}', '${msg.direction === 'inbound' ? activePhone : 'You'}', '${msg.body.replace(/'/g, "\\'")}')" title="Reply"><i class="bi bi-reply-fill"></i></div>
                            <div class="action-btn" onclick="prepareForward('${msg.body.replace(/'/g, "\\'")}', '${msg.media_path || ''}')" title="Forward"><i class="bi bi-share-fill"></i></div>
                        </div>
                        ${content}
                        <div class="message-time">${formatTime(msg.created_at)}</div>
                    `;
                    messageArea.appendChild(div);
                });
                messageArea.scrollTop = messageArea.scrollHeight;
            } catch (e) {
                console.error("Error loading history:", e);
                if (activePhone === phoneAtStart && !lastMessagesJson) {
                    document.getElementById('messageArea').innerHTML = '<div class="alert alert-danger m-3">Failed to load chat history.</div>';
                }
            } finally {
                isFetchingHistory = false;
            }
        }

        document.getElementById('sendBtn').onclick = sendMessage;
        document.getElementById('messageInput').onkeypress = (e) => {
            if (e.key === 'Enter') sendMessage();
        };

        let activeReplyId = null;

        async function sendMessage() {
            const input = document.getElementById('messageInput');
            const body = input.value.trim();
            const imageInput = document.getElementById('imageInput');
            const imageFile = imageInput.files[0];

            if (!body && !imageFile) return;

            const formData = new FormData();
            formData.append('instance_id', instanceId);
            formData.append('phone', activePhone);
            formData.append('body', body || '');
            if (activeReplyId) {
                formData.append('reply_to_id', activeReplyId);
            }
            if (imageFile) {
                formData.append('image', imageFile);
            }

            // Clear input immediately for better UX
            input.value = '';
            imageInput.value = '';
            previewContainer.style.display = 'none';
            cancelReply();

            try {
                const response = await fetch('/send-message', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();
                if (!result.success) {
                    alert(result.error || "Failed to send message");
                }

                // Reload history and recent chats
                await loadChatHistory();
                await loadRecentChats();
            } catch (e) {
                console.error("Error sending message:", e);
                alert("Connection error. Please try again.");
            }
        }

        function prepareReply(messageId, sender, text) {
            activeReplyId = messageId;
            document.getElementById('replySender').innerText = sender;
            document.getElementById('replyText').innerText = text;
            document.getElementById('replyContainer').style.display = 'flex';
            document.getElementById('messageInput').focus();
        }

        function cancelReply() {
            activeReplyId = null;
            document.getElementById('replyContainer').style.display = 'none';
        }

        document.getElementById('closeReply').onclick = cancelReply;

        let forwardContent = { body: '', mediaPath: '' };
        const forwardModal = new bootstrap.Modal(document.getElementById('forwardModal'));

        function prepareForward(body, mediaPath) {
            forwardContent = { body, mediaPath };
            loadForwardChats();
            forwardModal.show();
        }

        async function loadForwardChats(filter = '') {
            const response = await fetch(`/chat/recent?instance_id=${instanceId}`);
            const chats = await response.json();
            const list = document.getElementById('forwardChatList');
            list.innerHTML = '';

            chats.filter(chat => chat.phone.includes(filter)).forEach(chat => {
                const div = document.createElement('div');
                div.className = 'chat-item';
                div.onclick = () => selectForwardTarget(chat.phone, div);
                div.innerHTML = `
                    <div class="chat-avatar" style="width: 40px; height: 40px;"><i class="bi bi-person-circle"></i></div>
                    <div class="chat-info">
                        <div class="chat-name">${chat.phone}</div>
                    </div>
                `;
                list.appendChild(div);
            });
        }

        document.getElementById('forwardSearch').oninput = (e) => {
            loadForwardChats(e.target.value);
        };

        let selectedForwardPhone = null;
        function selectForwardTarget(phone, element) {
            selectedForwardPhone = phone;
            document.querySelectorAll('#forwardChatList .chat-item').forEach(i => i.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('confirmForward').disabled = false;
        }

        document.getElementById('confirmForward').onclick = async () => {
            if (!selectedForwardPhone) return;

            const formData = new FormData();
            formData.append('instance_id', instanceId);
            formData.append('phone', selectedForwardPhone);
            formData.append('body', forwardContent.body);
            if (forwardContent.mediaPath) {
                // We pass the existing media path to the controller
                formData.append('existing_media_path', forwardContent.mediaPath);
            }

            try {
                await fetch('/send-message', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                });
                forwardModal.hide();
                alert("Message forwarded!");
            } catch (e) {
                alert("Failed to forward message");
            }
        };

        // Emoji Picker Logic
        const emojiBtn = document.getElementById('emojiBtn');
        const emojiPicker = document.getElementById('emojiPicker');
        const messageInput = document.getElementById('messageInput');

        emojiBtn.onclick = (e) => {
            e.stopPropagation();
            document.querySelector('.chat-input-area').classList.toggle('emoji-active');
        };

        emojiPicker.addEventListener('emoji-click', event => {
            messageInput.value += event.detail.unicode;
            messageInput.focus();
        });

        document.addEventListener('click', (e) => {
            if (!emojiPicker.contains(e.target) && e.target !== emojiBtn) {
                document.querySelector('.chat-input-area').classList.remove('emoji-active');
            }
        });

        // Image Preview Logic
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');
        const imageName = document.getElementById('imageName');
        const removePreview = document.getElementById('removePreview');

        document.getElementById('imageInput').onchange = function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imageName.textContent = file.name;
                    previewContainer.style.display = 'flex';
                }
                reader.readAsDataURL(file);
                messageInput.focus();
            }
        };

        removePreview.onclick = function () {
            document.getElementById('imageInput').value = '';
            previewContainer.style.display = 'none';
            messageInput.focus();
        };

        // New Chat Logic
        const newChatModal = new bootstrap.Modal(document.getElementById('newChatModal'));
        document.getElementById('newChatBtn').onclick = () => newChatModal.show();

        document.getElementById('startChatBtn').onclick = () => {
            const phoneInput = document.getElementById('newChatPhone');
            let phone = phoneInput.value.trim().replace(/\+/g, '').replace(/\s/g, '');
            if (!phone) return;

            selectChat(phone);
            newChatModal.hide();
            phoneInput.value = '';
        };

        // Search Filtering Logic
        document.getElementById('chatSearch').oninput = (e) => {
            const term = e.target.value.toLowerCase();
            const items = document.querySelectorAll('#chatList .chat-item');
            items.forEach(item => {
                const name = item.querySelector('.chat-name').innerText.toLowerCase();
                const msg = item.querySelector('.chat-last-msg').innerText.toLowerCase();
                if (name.includes(term) || msg.includes(term)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        };

        // Initial load and polling
        loadRecentChats();
        setInterval(loadRecentChats, 4000);
        setInterval(loadChatHistory, 2000);

    </script>
</body>

</html>