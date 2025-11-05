<?php
require_once 'header.php';
require_once '../../helpers.php';
require_once 'check_permission.php';

check_permission('view_routers'); // Reuse permission from routers page

if (!isset($_GET['router_id'])) {
    echo "<h1>Error: Router ID is required.</h1>";
    require_once 'footer.php';
    exit();
}

$routerId = $_GET['router_id'];

// Get router details for display
$routerStmt = $pdo->prepare("SELECT serial_number, model FROM routers WHERE id = ?");
$routerStmt->execute([$routerId]);
$router = $routerStmt->fetch(PDO::FETCH_ASSOC);

if (!$router) {
    echo "<h1>Error: Router not found.</h1>";
    require_once 'footer.php';
    exit();
}

// Get command logs for the router
$logsStmt = $pdo->prepare("
    SELECT cl.*, u.username
    FROM command_logs cl
    LEFT JOIN users u ON cl.user_id = u.id
    WHERE cl.router_id = ?
    ORDER BY cl.executed_at DESC
");
$logsStmt->execute([$routerId]);
$logs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Command Log for Router: <?php echo htmlspecialchars($router['serial_number']); ?> (<?php echo htmlspecialchars($router['model']); ?>)</h2>

<div class="card mt-4">
    <div class="card-header">
        Command History
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="commandLogTable">
            <thead>
                <tr>
                    <th>Executed At</th>
                    <th>Command</th>
                    <th>Status</th>
                    <th>Output</th>
                    <th>Executed By</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['executed_at']); ?></td>
                        <td>
                            <pre class="command-view" data-original-command="<?= htmlspecialchars($log['command']) ?>"><?= htmlspecialchars(obfuscate_password($log['command'])) ?></pre>
                            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_commands')): ?>
                                <button class="btn btn-secondary btn-sm reveal-btn">Reveal</button>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $log['status'] === 'success' ? 'badge-success' : 'badge-danger'; ?>">
                                <?php echo htmlspecialchars($log['status']); ?>
                            </span>
                        </td>
                        <td><pre><?php echo htmlspecialchars($log['output']); ?></pre></td>
                        <td><?php echo htmlspecialchars($log['username'] ?? 'System (Cron)'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#commandLogTable').DataTable({
        "order": [[ 0, "desc" ]] // Order by the first column (Executed At) descending
    });
});
</script>
