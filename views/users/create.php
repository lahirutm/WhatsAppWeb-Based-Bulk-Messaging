<!DOCTYPE html>
<html>

<head>
    <title>Create User - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Create New User</div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="/users/store">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select" required>
                                    <?php
                                    $currentUserRole = $_SESSION['role'];
                                    if ($currentUserRole === 'super_user'): ?>
                                        <option value="client">Client</option>
                                        <option value="reseller">Reseller</option>
                                        <option value="administrator">Administrator</option>
                                        <option value="super_user">Super User</option>
                                    <?php elseif ($currentUserRole === 'administrator'): ?>
                                        <option value="client">Client</option>
                                        <option value="reseller">Reseller</option>
                                    <?php elseif ($currentUserRole === 'reseller'): ?>
                                        <option value="client">Client</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Create User</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="/users">Back to Users</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>