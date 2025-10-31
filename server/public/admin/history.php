<?php
require_once('../../config.php');
include('header.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $history = $pdo->query("
        SELECT rc.executed_at, r.serial_number, c.description, rc.status, rc.output
        FROM router_commands rc
        JOIN routers r ON rc.router_id = r.id
        JOIN commands c ON rc.command_id = c.id
        ORDER BY rc.executed_at DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2>Command Execution History</h2>
<table class="table table-striped data-table">
    <thead>
        <tr>
            <th>Timestamp</th>
            <th>Router</th>
            <th>Command</th>
            <th>Status</th>
            <th>Output</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($history as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['executed_at']) ?></td>
            <td><?= htmlspecialchars($log['serial_number']) ?></td>
            <td><?= htmlspecialchars($log['description']) ?></td>
            <td><span class="badge badge-<?= ($log['status'] == 'success' || $log['status'] == 'delivered') ? 'success' : 'danger' ?>"><?= htmlspecialchars($log['status']) ?></span></td>
            <td><pre><?= htmlspecialchars($log['output']) ?></pre></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('footer.php'); ?>
