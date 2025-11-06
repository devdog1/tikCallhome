<?php
require_once('../../database.php');
require_once('../../helpers.php');
include('header.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: routers.php');
    exit;
}

// Fetch router details first
try {
    $stmt = $pdo->prepare("SELECT * FROM routers WHERE id = ?");
    $stmt->execute([$id]);
    $router = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$router) {
        header('Location: routers.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serial_number = $_POST['serial_number'];
    $name = $_POST['name'];
    $model = $_POST['model'];
    $ip_address = $_POST['ip_address'];
    $adopted = isset($_POST['adopted']) ? 1 : 0;

    try {
        // Check if the name has changed
        if ($name !== $router['name']) {
            $command = "/system identity set name=\"{$name}\"";
            $check_command = "/system identity print where name=\"{$name}\"";

            $stmt = $pdo->prepare("INSERT INTO commands (command, check_command, description, type, target) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$command, $check_command, "Set router name to {$name}", 'serial', $serial_number]);
        }

        $stmt = $pdo->prepare("UPDATE routers SET serial_number = ?, name = ?, model = ?, ip_address = ?, adopted = ? WHERE id = ?");
        $stmt->execute([$serial_number, $name, $model, $ip_address, $adopted, $id]);

        log_user_action($pdo, $_SESSION['user_id'], $id, "Router details updated");

        header('Location: routers.php');
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>

<h2>Edit Router</h2>
<form method="post">
    <div class="form-group">
        <label for="serial_number">Serial Number</label>
        <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?= htmlspecialchars($router['serial_number']) ?>" required>
    </div>
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($router['name']) ?>">
    </div>
    <div class="form-group">
        <label for="model">Model</label>
        <input type="text" class="form-control" id="model" name="model" value="<?= htmlspecialchars($router['model']) ?>" required>
    </div>
    <div class="form-group">
        <label for="ip_address">IP Address</label>
        <input type="text" class="form-control" id="ip_address" name="ip_address" value="<?= htmlspecialchars($router['ip_address']) ?>" required>
    </div>
    <div class="form-check">
        <input type="checkbox" class="form-check-input" id="adopted" name="adopted" <?= $router['adopted'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="adopted">Adopted</label>
    </div>

    <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_routers') && !empty($router['local_admin_password'])): ?>
    <div class="form-group mt-3">
        <label for="local_admin_password">Managed Admin Password</label>
        <input type="text" class="form-control" id="local_admin_password" value="<?= htmlspecialchars($router['local_admin_password']) ?>" readonly>
        <small class="form-text text-muted">This password is managed by the system. It was set during adoption.</small>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary mt-3">Update Router</button>
</form>

<?php include('footer.php'); ?>
