<?php
require_once('../../database.php');
include('header.php');

try {

    // Get router stats
    $adopted_count = $pdo->query("SELECT COUNT(*) FROM routers WHERE adopted = true")->fetchColumn();
    $pending_count = $pdo->query("SELECT COUNT(*) FROM routers WHERE adopted = false")->fetchColumn();
    $offline_count = $pdo->query("SELECT COUNT(*) FROM routers WHERE last_seen < NOW() - INTERVAL '5 minutes'")->fetchColumn();

    // Get recent history
    $recent_history = $pdo->query("
        SELECT rc.executed_at, r.serial_number, c.description, rc.status
        FROM router_commands rc
        JOIN routers r ON rc.router_id = r.id
        JOIN commands c ON rc.command_id = c.id
        ORDER BY rc.executed_at DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Adopted Routers</div>
            <div class="card-body">
                <h5 class="card-title"><?= $adopted_count ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Pending Adoption</div>
            <div class="card-body">
                <h5 class="card-title"><?= $pending_count ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Offline Routers (5+ min)</div>
            <div class="card-body">
                <h5 class="card-title"><?= $offline_count ?></h5>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h2>Recent Command History</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Router</th>
                    <th>Command</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_history as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['executed_at']) ?></td>
                    <td><?= htmlspecialchars($log['serial_number']) ?></td>
                    <td><?= htmlspecialchars($log['description']) ?></td>
                    <td><span class="badge badge-<?= ($log['status'] == 'success' || $log['status'] == 'delivered') ? 'success' : 'danger' ?>"><?= htmlspecialchars($log['status']) ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
