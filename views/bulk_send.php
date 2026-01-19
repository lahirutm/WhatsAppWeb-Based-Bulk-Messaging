<!DOCTYPE html>
<html>

<head>
    <title>Bulk Send - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Bulk Send Message</div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($instanceStatus !== 'connected'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>WhatsApp not connected!</strong> Please connect your instance from the <a
                                    href="/dashboard" class="alert-link">dashboard</a> before sending bulk messages.
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/bulk-send" enctype="multipart/form-data">
                            <input type="hidden" name="instance_id" value="<?php echo $instanceId; ?>">

                            <div class="mb-3">
                                <label class="form-label">Upload CSV (One number per line)</label>
                                <input type="file" name="csv_file" class="form-control" accept=".csv">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Or Paste Numbers (One per line)</label>
                                <textarea name="numbers_text" class="form-control" rows="5"
                                    placeholder="15551234567&#10;15559876543"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Message Template (Optional)</label>
                                <select id="templateSelect" class="form-select">
                                    <option value="">-- Select a Template --</option>
                                    <?php foreach ($templates as $tpl): ?>
                                        <option value="<?php echo $tpl['id']; ?>">
                                            <?php echo htmlspecialchars($tpl['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Message Body</label>
                                <textarea name="body" id="messageBody" class="form-control" rows="4"
                                    required></textarea>
                                <small class="text-muted">Supports *bold*, _italic_, ~strike~</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image (Optional)</label>
                                <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
                            </div>

                            <!-- Scheduling Option -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="scheduleCheckbox">
                                    <label class="form-check-label" for="scheduleCheckbox">
                                        Schedule this bulk message
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3" id="scheduleTimeDiv" style="display: none;">
                                <label class="form-label">Scheduled Time</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduledAt" class="form-control">
                                <small class="text-muted">All messages will be sent at the specified time</small>
                            </div>

                            <!-- Live Preview -->
                            <div class="mb-3">
                                <label class="form-label">Live Preview</label>
                                <div class="card" style="max-width: 300px; background-color: #dcf8c6;">
                                    <div class="card-body">
                                        <img id="imagePreview" src=""
                                            style="width: 100%; display: none; margin-bottom: 10px; border-radius: 5px;">
                                        <p id="textPreview" style="white-space: pre-wrap; margin-bottom: 0;"></p>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" <?php echo ($instanceStatus !== 'connected') ? 'disabled' : ''; ?>>Create Batch & Send</button>
                            <a href="/dashboard" class="btn btn-secondary">Back to Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const messageBody = document.getElementById('messageBody');
        const textPreview = document.getElementById('textPreview');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const scheduleCheckbox = document.getElementById('scheduleCheckbox');
        const scheduleTimeDiv = document.getElementById('scheduleTimeDiv');
        const scheduledAt = document.getElementById('scheduledAt');
        const templateSelect = document.getElementById('templateSelect');

        // Template selection logic
        templateSelect.addEventListener('change', function () {
            const templateId = this.value;
            if (templateId) {
                fetch('/templates/get/' + templateId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            messageBody.value = data.body;
                            // Trigger input event to update preview
                            messageBody.dispatchEvent(new Event('input'));
                        }
                    })
                    .catch(error => console.error('Error fetching template:', error));
            }
        });

        // Toggle schedule time picker
        scheduleCheckbox.addEventListener('change', function () {
            if (this.checked) {
                scheduleTimeDiv.style.display = 'block';
                scheduledAt.required = true;
                // Set minimum datetime to now
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                scheduledAt.min = now.toISOString().slice(0, 16);
            } else {
                scheduleTimeDiv.style.display = 'none';
                scheduledAt.required = false;
                scheduledAt.value = '';
            }
        });

        messageBody.addEventListener('input', function () {
            let text = this.value;
            // Simple formatting replacement
            text = text.replace(/\*(.*?)\*/g, '<b>$1</b>');
            text = text.replace(/_(.*?)_/g, '<i>$1</i>');
            text = text.replace(/~(.*?)~/g, '<strike>$1</strike>');
            textPreview.innerHTML = text;
        });

        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>