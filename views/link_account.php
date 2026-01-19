<!DOCTYPE html>
<html>

<head>
    <title>Link Account - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card">
                    <div class="card-header">Link WhatsApp Account</div>
                    <div class="card-body">
                        <p>Scan the QR code below with your WhatsApp mobile app.</p>
                        <div id="qrcode" class="d-flex justify-content-center my-4"></div>
                        <div id="status" class="alert alert-info">Waiting for QR code...</div>
                        <a href="/dashboard" class="btn btn-secondary">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const socket = io('http://localhost:3000'); // Connect to Node.js service
        const instanceId = <?php echo $instanceId; ?>;
        const sessionName = '<?php echo $sessionName; ?>';

        socket.on('connect', () => {
            console.log('Connected to socket server');
            document.getElementById('status').innerText = 'Connecting to WhatsApp service...';

            // Request to start session
            socket.emit('start_session', { instanceId, sessionName });
        });

        socket.on('qr', (qrCode) => {
            console.log('QR Received');
            document.getElementById('status').innerText = 'Scan this QR code';
            document.getElementById('qrcode').innerHTML = '';
            new QRCode(document.getElementById('qrcode'), qrCode);
        });

        socket.on('ready', (msg) => {
            document.getElementById('status').className = 'alert alert-success';
            document.getElementById('status').innerText = 'Connected successfully! Redirecting...';
            setTimeout(() => {
                window.location.href = '/dashboard';
            }, 2000);
        });

        socket.on('authenticated', (msg) => {
            document.getElementById('status').innerText = 'Authenticated! Waiting for ready...';
        });

        socket.on('auth_failure', (msg) => {
            document.getElementById('status').className = 'alert alert-danger';
            document.getElementById('status').innerText = 'Authentication failed. Please try again.';
        });
    </script>
</body>

</html>