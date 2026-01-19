<!DOCTYPE html>
<html>

<head>
    <title>Manage Users - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Users</h2>
            <a href="/users/create" class="btn btn-primary">Create New User</a>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Credits</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <?php echo $user['id']; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($user['name'] ?? '-'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php
                                    echo match ($user['role']) {
                                        'super_user' => 'danger',
                                        'administrator' => 'warning',
                                        'reseller' => 'info',
                                        'client' => 'success',
                                        default => 'secondary'
                                    };
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['is_enabled'] ? 'success' : 'danger'; ?>">
                                        <?php echo $user['is_enabled'] ? 'Active' : 'Disabled'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo number_format($user['message_credits']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $user['created_at']; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/users/toggle/<?php echo $user['id']; ?>"
                                            class="btn btn-sm btn-<?php echo $user['is_enabled'] ? 'warning' : 'success'; ?>">
                                            <?php echo $user['is_enabled'] ? 'Disable' : 'Enable'; ?>
                                        </a>
                                        <a href="/users/password/<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                            Password
                                        </a>
                                        <a href="/users/assign-package/<?php echo $user['id']; ?>"
                                            class="btn btn-sm btn-success">
                                            Assign Package
                                        </a>
                                    </div>
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