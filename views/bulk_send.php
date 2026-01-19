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
                                <label class="form-label">Message Body</label>
                                <textarea name="body" id="messageBody" class="form-control" rows="4"
                                    required></textarea>
                                <small class="text-muted">Supports *bold*, _italic_, ~strike~</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image (Optional)</label>
                                <input type="file" name="image" id="imageInput" class="form-control" accept="image/*">
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

                            <button type="submit" class="btn btn-primary">Create Batch & Send</button>
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
</body>

</html>