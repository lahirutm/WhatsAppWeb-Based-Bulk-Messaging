<!DOCTYPE html>
<html>

<head>
    <title>Dashboard - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="container mt-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <strong>Your Message Credits:</strong> <?php echo number_format($userData['message_credits']); ?>
            </div>
            <small class="text-muted">Credits are deducted when you send messages</small>
        </div>

        <h2>My Instances</h2>
        <a href="/link-account" class="btn btn-success mb-3">Link New Account</a>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Session Name</th>
                    <th>Phone Number</th>
                    <th>Status</th>
                    <th>Messages Sent</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($instances as $instance): ?>
                    <tr>
                        <td>
                            <?php echo $instance['id']; ?>
                        </td>
                        <td>
                            <?php echo $instance['session_name']; ?>
                        </td>
                        <td>
                            <?php echo $instance['phone'] ? '+' . $instance['phone'] : '-'; ?>
                        </td>
                        <td>
                            <span
                                class="badge bg-<?php echo $instance['status'] == 'connected' ? 'success' : 'secondary'; ?>">
                                <?php echo $instance['status']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo $instance['sent_count'] ?? 0; ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $instance['created_at']; ?>
                        </td>
                        <td>
                            <a href="/send-message?instance_id=<?php echo $instance['id']; ?>"
                                class="btn btn-primary btn-sm">Send Message</a>
                            <a href="/bulk-send?instance_id=<?php echo $instance['id']; ?>"
                                class="btn btn-warning btn-sm">Bulk Send</a>
                            <?php if ($instance['status'] == 'connected'): ?>
                                <form method="POST" action="/disconnect" style="display:inline;">
                                    <input type="hidden" name="instance_id" value="<?php echo $instance['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">Disconnect</button>
                                </form>
                            <?php else: ?>
                                <a href="/link-account?instance_id=<?php echo $instance['id']; ?>"
                                    class="btn btn-info btn-sm">Re-link</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>