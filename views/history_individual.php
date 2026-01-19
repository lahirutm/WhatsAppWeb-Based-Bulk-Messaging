<!DOCTYPE html>
<html>

<head>
    <title>Individual History - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="container mt-4">
        <h2>Individual Message History</h2>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control"
                            value="<?php echo htmlspecialchars($_GET['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control"
                            value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control"
                            value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="/history/individual" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-striped bg-white">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Media</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Sent At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td><?php echo $msg['id']; ?></td>
                            <td><?php echo htmlspecialchars($msg['phone']); ?></td>
                            <td><?php echo htmlspecialchars(substr($msg['body'], 0, 50)) . (strlen($msg['body']) > 50 ? '...' : ''); ?>
                            </td>
                            <td><?php echo $msg['media_path'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <span class="badge bg-<?php
                                echo match ($msg['status']) {
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    default => 'warning'
                                };
                                ?>">
                                    <?php echo $msg['status']; ?>
                                </span>
                                <?php if ($msg['is_api']): ?>
                                    <span class="badge bg-info text-dark">API</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $msg['created_at']; ?></td>
                            <td><?php echo $msg['sent_at']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-info preview-btn"
                                    data-body="<?php echo htmlspecialchars($msg['body']); ?>"
                                    data-media="<?php echo htmlspecialchars($msg['media_path'] ?? ''); ?>">
                                    Preview
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Message Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalMedia" class="mb-3 text-center" style="display: none;">
                        <img src="" style="max-width: 100%; border-radius: 5px;">
                    </div>
                    <p id="modalBody" style="white-space: pre-wrap;"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
            const modalBody = document.getElementById('modalBody');
            const modalMedia = document.getElementById('modalMedia');
            const modalImage = modalMedia.querySelector('img');

            document.querySelectorAll('.preview-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const body = this.dataset.body;
                    const media = this.dataset.media;

                    modalBody.textContent = body;

                    if (media) {
                        modalImage.src = media;
                        modalMedia.style.display = 'block';
                    } else {
                        modalMedia.style.display = 'none';
                    }

                    previewModal.show();
                });
            });
        });
    </script>
</body>

</html>