<!DOCTYPE html>
<html>

<head>
    <title>Manage Packages - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Credit Packages</h2>
            <a href="/packages/create" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Create New Package
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Credits</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $pkg): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($pkg['name']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <?php echo number_format($pkg['credits']); ?>
                                </td>
                                <td>
                                    <?php if ($pkg['is_default']): ?>
                                        <span class="badge bg-primary">Default</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if (!$pkg['is_default']): ?>
                                        <a href="/packages/set-default/<?php echo $pkg['id']; ?>"
                                            class="btn btn-sm btn-outline-primary me-1" title="Set as Default">
                                            <i class="bi bi-star"></i>
                                        </a>
                                        <a href="/packages/delete/<?php echo $pkg['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this package?')"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary disabled"
                                            title="Cannot delete default package">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>