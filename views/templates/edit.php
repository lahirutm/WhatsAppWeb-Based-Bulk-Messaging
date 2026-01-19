<!DOCTYPE html>
<html>

<head>
    <title>Edit Template - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Edit Template</div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/templates/update/<?php echo $template['id']; ?>">
                            <div class="mb-3">
                                <label class="form-label">Template Name</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?php echo htmlspecialchars($template['name']); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message Body</label>
                                <textarea name="body" class="form-control" rows="8"
                                    required><?php echo htmlspecialchars($template['body']); ?></textarea>
                                <small class="text-muted">Supports *bold*, _italic_, ~strike~</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Template</button>
                            <a href="/templates" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>