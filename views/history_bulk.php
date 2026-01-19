<!DOCTYPE html>
<html>

<head>
    <title>Bulk History - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <div class="container mt-4">
        <h2>Bulk Message History</h2>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control"
                            value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control"
                            value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="/history/bulk" class="btn btn-secondary">Reset</a>
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
                        <th>Batch Name</th>
                        <th>Total Messages</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($batches as $batch): ?>
                        <tr>
                            <td>
                                <?php echo $batch['id']; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($batch['name']); ?>
                            </td>
                            <td>
                                <?php echo $batch['total_count']; ?>
                            </td>
                            <td>
                                <?php echo $batch['created_at']; ?>
                            </td>
                            <td>
                                <a href="/batch-status?batch_id=<?php echo $batch['id']; ?>"
                                    class="btn btn-info btn-sm">View Status</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>