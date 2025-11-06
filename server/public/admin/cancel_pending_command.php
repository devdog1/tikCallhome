<?php
require_once '../../database.php';
require_once '../../helpers.php';
require_once 'check_permission.php';

check_permission('manage_commands');

$router_id = $_GET['router_id'] ?? null;
$command_id = $_GET['command_id'] ?? null;

if (!$router_id || !$command_id) {
    header("Location: pending_commands.php?error=Missing router or command ID.");
    exit();
}

try {
    // Mark the command as 'cancelled' in router_commands
    $stmt = $pdo->prepare("INSERT INTO router_commands (router_id, command_id, executed_at, status, output) VALUES (?, ?, NOW(), 'cancelled', 'Manually cancelled by user.')");
    $stmt->execute([$router_id, $command_id]);

    // Log the user action
    $user_id = $_SESSION['user_id'] ?? null;
    $details = json_encode(['router_id' => $router_id, 'command_id' => $command_id]);
    log_user_action($pdo, $user_id, $router_id, "Cancelled pending command", $details);

    header("Location: pending_commands.php?success=Command cancelled.");
    exit();
} catch (PDOException $e) {
    header("Location: pending_commands.php?error=Database error: " . urlencode($e->getMessage()));
    exit();
}
?>
