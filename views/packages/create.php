<!DOCTYPE html>
<html>

<head>
    <title>Create Package - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Create New Credit Package</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/packages/store">
                            <div class="mb-3">
                                <label class="form-label">Package Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Premium Pack"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Credit Amount</label>
                                <input type="number" name="credits" class="form-control" placeholder="e.g. 500" required
                                    min="1">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Create Package</button>
                                <a href="/packages" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>