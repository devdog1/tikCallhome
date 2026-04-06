<?php
require_once(__DIR__ . '/../database.php');
require_once(__DIR__ . '/../helpers.php');

/**
 * Task scheduler script to remove pending routers for adoption
 * that have not reported for more than 1 week.
 */

try {
    // Determine the interval syntax based on the database type
    // $dbType is defined in config.php, which is included by database.php
    if ($dbType === 'mysql') {
        $interval = "INTERVAL 7 DAY";
        $sql = "SELECT id, serial_number FROM routers WHERE adopted = false AND last_seen < NOW() - $interval";
    } elseif ($dbType === 'pgsql') {
        $interval = "INTERVAL '7 days'";
        $sql = "SELECT id, serial_number FROM routers WHERE adopted = false AND last_seen < NOW() - $interval";
    } else {
        die("Unsupported database type: $dbType\n");
    }

    $pdo->beginTransaction();

    $stmt = $pdo->query($sql);
    $routersToDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($routersToDelete) > 0) {
        foreach ($routersToDelete as $router) {
            $routerId = $router['id'];
            $serialNumber = $router['serial_number'];

            // Delete associated router_commands first (though pending routers shouldn't have many, if any)
            $pdo->prepare("DELETE FROM router_commands WHERE router_id = ?")->execute([$routerId]);

            // Delete the router
            $pdo->prepare("DELETE FROM routers WHERE id = ?")->execute([$routerId]);

            // Log the action
            log_user_action($pdo, null, null, "System Cleanup", "Deleted pending router $serialNumber due to inactivity (over 1 week).");

            echo "Deleted pending router: $serialNumber\n";
        }
        echo "Cleanup complete. " . count($routersToDelete) . " router(s) removed.\n";
    } else {
        echo "No inactive pending routers found.\n";
    }

    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error during cleanup: " . $e->getMessage() . "\n";
    exit(1);
}
