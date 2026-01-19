<!DOCTYPE html>
<html>

<head>
    <title>API Settings - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="container mt-4">
        <h2>API Settings</h2>
        <p class="text-muted">Manage API keys for your linked WhatsApp instances.</p>

        <div class="card mb-4">
            <div class="card-header">Your Instances</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Instance ID</th>
                                <th>Phone Number</th>
                                <th>Status</th>
                                <th>API Key</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($instances as $instance): ?>
                                <tr>
                                    <td>
                                        <?php echo $instance['id']; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($instance['phone'] ?? 'Not Linked'); ?>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-<?php echo $instance['status'] == 'connected' ? 'success' : 'secondary'; ?>">
                                            <?php echo $instance['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($instance['api_key']): ?>
                                            <div class="input-group">
                                                <input type="text" class="form-control form-control-sm"
                                                    value="<?php echo $instance['api_key']; ?>" readonly
                                                    id="key-<?php echo $instance['id']; ?>">
                                                <button class="btn btn-outline-secondary btn-sm"
                                                    onclick="copyKey('<?php echo $instance['id']; ?>')">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No Key Generated</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $instance['api_key_created_at'] ? $instance['api_key_created_at'] : '-'; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="/api-settings/generate"
                                            onsubmit="return confirm('Generating a new key will invalidate the old one. Continue?');">
                                            <input type="hidden" name="instance_id" value="<?php echo $instance['id']; ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <?php echo $instance['api_key'] ? 'Regenerate Key' : 'Generate Key'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">API Documentation</div>
            <div class="card-body">
                <h5>Send Message Endpoint</h5>
                <p>Use this endpoint to send messages programmatically.</p>

                <div class="alert alert-info">
                    <strong>POST</strong>
                    <?php echo 'http://' . $_SERVER['HTTP_HOST'] . '/api/send'; ?>
                </div>

                <h6>Request Body (JSON)</h6>
                <pre class="bg-dark text-white p-3 rounded">
{
    "api_key": "YOUR_API_KEY",
    "sender": "LINKED_PHONE_NUMBER",
    "number": "RECIPIENT_NUMBER",
    "message": "Hello World"
}</pre>

                <ul>
                    <li><code>api_key</code>: The API key generated above for the instance.</li>
                    <li><code>sender</code>: The phone number of the linked instance (must match the key).</li>
                    <li><code>number</code>: The recipient's phone number with country code (e.g., 15551234567).</li>
                    <li><code>message</code>: The text message to send.</li>
                </ul>

                <h6>Response</h6>
                <pre class="bg-dark text-white p-3 rounded">
{
    "status": "success",
    "message": "Message queued successfully",
    "message_id": 123
}</pre>
            </div>
        </div>
    </div>

    <script>
        function copyKey(id) {
            const copyText = document.getElementById("key-" + id);
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(copyText.value);
            alert("API Key copied to clipboard");
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>