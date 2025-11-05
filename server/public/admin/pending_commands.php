<?php
require_once 'header.php';
require_once '../../helpers.php';
require_once 'check_permission.php';

check_permission('view_commands'); // Reuse 'view_commands' permission

// This query identifies commands that have not yet been executed on the relevant routers.
// It checks all applicable command types (generic, group, model, serial) and filters out
// any that already have a corresponding entry in the router_commands table.
$stmt = $pdo->prepare("
    SELECT
        r.serial_number,
        r.model,
        c.command,
        c.description,
        g.name as group_name
    FROM routers r
    JOIN commands c ON
        (c.type = 'generic') OR
        (c.type = 'group' AND c.target = r.group_id) OR
        (c.type = 'model' AND c.target = r.model) OR
        (c.type = 'serial' AND c.target = r.serial_number)
    LEFT JOIN router_commands rc ON c.id = rc.command_id AND rc.router_id = r.id
    LEFT JOIN groups g ON r.group_id = g.id
    WHERE r.adopted = true AND rc.id IS NULL
    ORDER BY r.serial_number, c.id
");
$stmt->execute();
$pending_commands = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Pending Commands</h2>
<p>This page shows commands that are scheduled to be applied to routers on the next execution run, but have not yet been delivered.</p>

<div class="card mt-4">
    <div class="card-header">
        Pending Commands by Router
    </div>
    <div class="card-body">
        <table class="table table-bordered table-striped" id="pendingCommandsTable">
            <thead>
                <tr>
                    <th>Router Serial</th>
                    <th>Router Model</th>
                    <th>Group</th>
                    <th>Command Description</th>
                    <th>Command</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_commands as $pc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pc['serial_number']); ?></td>
                        <td><?php echo htmlspecialchars($pc['model']); ?></td>
                        <td><?php echo htmlspecialchars($pc['group_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($pc['description']); ?></td>
                        <td>
                            <pre class="command-view" data-original-command="<?= htmlspecialchars($pc['command']) ?>"><?= htmlspecialchars(obfuscate_password($pc['command'])) ?></pre>
                            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_commands')): ?>
                                <button class="btn btn-secondary btn-sm reveal-btn">Reveal</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                 <?php if (empty($pending_commands)): ?>
                    <tr>
                        <td colspan="5" class="text-center">No pending commands found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>

<script>
$(document).ready(function() {
    $('#pendingCommandsTable').DataTable();
});
</script>
