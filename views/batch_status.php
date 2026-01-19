<!DOCTYPE html>
<html>

<head>
    <title>Batch Status - WhatsApp App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="5"> <!-- Auto refresh every 5 seconds -->
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                Batch Status:
                <?php echo $batch['name']; ?>
                <span class="float-end">
                    <?php echo $batch['created_at']; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h3>
                                    <?php echo $batch['total_count']; ?>
                                </h3>
                                <small>Total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h3>
                                    <?php echo $pending; ?>
                                </h3>
                                <small>Pending</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h3>
                                    <?php echo $sent; ?>
                                </h3>
                                <small>Sent</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h3>
                                    <?php echo $failed; ?>
                                </h3>
                                <small>Failed</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="progress mb-4" style="height: 30px;">
                    <?php
                    $percent = $batch['total_count'] > 0 ? ($sent / $batch['total_count']) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percent; ?>%">
                        <?php echo round($percent); ?>%
                    </div>
                </div>

                <?php if (!empty($messages)):
                    $previewMsg = $messages[0];
                    ?>
                    <div class="card mb-4">
                        <div class="card-header">Message Preview</div>
                        <div class="card-body">
                            <?php if ($previewMsg['media_path']): ?>
                                <div class="mb-3">
                                    <img src="<?php echo htmlspecialchars($previewMsg['media_path']); ?>"
                                        style="max-width: 200px; border-radius: 5px;">
                                </div>
                            <?php endif; ?>
                            <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($previewMsg['body']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <h4>Message Details</h4>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Sent At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $msg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($msg['phone']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php
                                        echo match ($msg['status']) {
                                            'sent' => 'success',
                                            'failed' => 'danger',
                                            default => 'warning'
                                        };
                                        ?>">
                                            <?php echo $msg['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $msg['sent_at'] ? $msg['sent_at'] : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <a href="/history/bulk" class="btn btn-secondary mt-3">Back to Bulk History</a>
            </div>
        </div>
    </div>
</body>

</html>