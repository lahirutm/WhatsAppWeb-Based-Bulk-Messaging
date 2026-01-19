<!DOCTYPE html>
<html>

<head>
    <title>Assign Package - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/../partials/nav.php'; ?>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Assign Credit Package</h2>
                    <a href="/users" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Package assigned successfully! Credits have been added to the user's balance.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error']) && $_GET['error'] === 'insufficient_credits'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Insufficient Credits!</strong> You do not have enough credits in your account to assign this
                        package.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Assign Package to <?php echo htmlspecialchars($user['username']); ?></h5>
                        <?php if ($resellerBalance !== null): ?>
                            <span class="badge bg-light text-primary">Your Balance:
                                <?php echo number_format($resellerBalance); ?> Credits</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <p class="mb-0 text-muted">Current Balance</p>
                                <h3 class="mb-0 text-primary"><?php echo number_format($user['message_credits']); ?>
                                    <small style="font-size: 14px;">Credits</small>
                                </h3>
                            </div>
                            <div class="col-md-8 border-start">
                                <form method="POST" action="/users/assign-package/<?php echo $user['id']; ?>"
                                    class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Select Package</label>
                                        <select name="package_id" class="form-select" required>
                                            <option value="">-- Choose a Package --</option>
                                            <?php foreach ($packages as $pkg): ?>
                                                <option value="<?php echo $pkg['id']; ?>">
                                                    <?php echo htmlspecialchars($pkg['name']); ?>
                                                    (<?php echo number_format($pkg['credits']); ?> Credits)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100">Assign Package</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Package Activation Log</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Package</th>
                                    <th>Credits</th>
                                    <th>Activated By</th>
                                    <th>Activated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted">No packages assigned yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($log['package_name']); ?></strong></td>
                                            <td><?php echo number_format($log['package_credits']); ?></td>
                                            <td><?php echo htmlspecialchars($log['admin_name']); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($log['assigned_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>