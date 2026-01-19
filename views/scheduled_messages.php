<!DOCTYPE html>
<html>

<head>
    <title>Scheduled Messages - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h4>Scheduled Messages</h4>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($scheduledMessages)): ?>
                    <div class="alert alert-info">
                        No scheduled messages found. You can schedule messages from the Send Message or Bulk Send pages.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Recipient/Batch</th>
                                    <th>Message Preview</th>
                                    <th>Scheduled Time</th>
                                    <th>Instance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scheduledMessages as $msg): ?>
                                    <tr>
                                        <td>
                                            <?php echo $msg['id']; ?>
                                        </td>
                                        <td>
                                            <?php if ($msg['batch_id']): ?>
                                                <span class="badge bg-primary">Bulk</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Individual</span>
                                            <?php endif; ?>
                                            <?php if ($msg['is_api']): ?>
                                                <span class="badge bg-secondary">API</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($msg['batch_id']): ?>
                                                <strong>
                                                    <?php echo htmlspecialchars($msg['batch_name']); ?>
                                                </strong>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($msg['phone']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div
                                                style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars(substr($msg['body'], 0, 50)); ?>
                                                <?php if (strlen($msg['body']) > 50)
                                                    echo '...'; ?>
                                            </div>
                                            <?php if ($msg['media_path']): ?>
                                                <small class="text-muted">📷 Has image</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>
                                                <?php echo date('Y-m-d H:i', strtotime($msg['scheduled_at'])); ?>
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php
                                                $diff = strtotime($msg['scheduled_at']) - time();
                                                if ($diff > 0) {
                                                    $hours = floor($diff / 3600);
                                                    $minutes = floor(($diff % 3600) / 60);
                                                    echo "in {$hours}h {$minutes}m";
                                                } else {
                                                    echo "Processing...";
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo htmlspecialchars($msg['instance_phone'] ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-warning"
                                                    onclick="rescheduleMessage(<?php echo $msg['id']; ?>, '<?php echo $msg['scheduled_at']; ?>')">
                                                    Reschedule
                                                </button>
                                                <form method="POST"
                                                    action="/scheduled-messages/cancel/<?php echo $msg['id']; ?>"
                                                    style="display: inline;"
                                                    onsubmit="return confirm('Cancel this scheduled message? Credit will be refunded.');">
                                                    <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <a href="/dashboard" class="btn btn-secondary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="rescheduleForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Scheduled Time</label>
                            <input type="datetime-local" name="scheduled_at" id="newScheduledAt" class="form-control"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Reschedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const rescheduleModal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
        const rescheduleForm = document.getElementById('rescheduleForm');
        const newScheduledAt = document.getElementById('newScheduledAt');

        function rescheduleMessage(id, currentTime) {
            rescheduleForm.action = '/scheduled-messages/reschedule/' + id;

            // Set minimum datetime to now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            newScheduledAt.min = now.toISOString().slice(0, 16);

            // Set current value
            const current = new Date(currentTime);
            current.setMinutes(current.getMinutes() - current.getTimezoneOffset());
            newScheduledAt.value = current.toISOString().slice(0, 16);

            rescheduleModal.show();
        }

        // Auto-refresh every 60 seconds to update time remaining
        setTimeout(function () {
            location.reload();
        }, 60000);
    </script>
</body>

</html>