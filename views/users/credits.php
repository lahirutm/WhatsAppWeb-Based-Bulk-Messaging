<!DOCTYPE html>
<html>

<head>
    <title>Manage Credits - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Manage Credits for:
                            <?php echo htmlspecialchars($user['username']); ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Current Credits:</strong>
                            <?php echo number_format($user['message_credits']); ?>
                        </div>

                        <form method="POST" action="/users/credits/<?php echo $user['id']; ?>">
                            <div class="mb-3">
                                <label for="action" class="form-label">Action</label>
                                <select class="form-select" id="action" name="action" required>
                                    <option value="add">Add Credits</option>
                                    <option value="set">Set Credits</option>
                                </select>
                                <small class="text-muted">
                                    "Add" will add to existing credits. "Set" will replace the current balance.
                                </small>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" min="0" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Update Credits</button>
                                <a href="/users" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>