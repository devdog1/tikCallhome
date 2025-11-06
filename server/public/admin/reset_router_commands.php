<?php
require_once '../../database.php';
require_once '../../helpers.php';
require_once 'check_permission.php';

check_permission('manage_commands');

$router_id = $_GET['router_id'] ?? null;

if (!$router_id) {
    header("Location: routers.php?error=Missing router ID.");
    exit();
}

try {
    // Delete all command history for this router
    $stmt = $pdo->prepare("DELETE FROM router_commands WHERE router_id = ?");
    $stmt->execute([$router_id]);

    // Log the user action
    $user_id = $_SESSION['user_id'] ?? null;
    $details = json_encode(['router_id' => $router_id]);
    log_user_action($pdo, $user_id, $router_id, "Reset all router commands", $details);

    header("Location: routers.php?success=Router commands reset successfully.");
    exit();
} catch (PDOException $e) {
    header("Location: routers.php?error=Database error: " . urlencode($e->getMessage()));
    exit();
}
?>
