<?php
// Include the configuration file
require_once('../../config.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submissions for creating groups and assigning routers
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_group'])) {
            $stmt = $pdo->prepare("INSERT INTO groups (name) VALUES (:name)");
            $stmt->execute(['name' => $_POST['group_name']]);
        } elseif (isset($_POST['assign_router'])) {
            $stmt = $pdo->prepare("UPDATE routers SET group_id = :group_id WHERE id = :router_id");
            $stmt->execute(['group_id' => $_POST['group_id'], 'router_id' => $_POST['router_id']]);
        }
        header("Location: groups.php");
        exit;
    }

    $groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $routers = $pdo->query("SELECT r.*, g.name as group_name FROM routers r LEFT JOIN groups g ON r.group_id = g.id WHERE r.adopted = true ORDER BY r.serial_number")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Group Management</title>
</head>
<body>
    <h1>Mikrotik Management</h1>
    <hr>
    <h2><a href="index.php">Routers & Commands</a> | <a href="groups.php">Groups</a> | <a href="wifi.php">WiFi Configs</a></h2>
    <hr>

    <h2>Create New Group</h2>
    <form action="groups.php" method="post">
        <label for="group_name">Group Name:</label>
        <input type="text" id="group_name" name="group_name" required>
        <input type="submit" name="add_group" value="Create Group">
    </form>

    <h2>Existing Groups</h2>
    <ul>
        <?php foreach ($groups as $group): ?>
            <li><?= htmlspecialchars($group['name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Assign Routers to Groups</h2>
    <table border="1">
        <tr>
            <th>Serial Number</th>
            <th>Model</th>
            <th>Current Group</th>
            <th>Assign to Group</th>
        </tr>
        <?php foreach ($routers as $router): ?>
        <tr>
            <td><?= htmlspecialchars($router['serial_number']) ?></td>
            <td><?= htmlspecialchars($router['model']) ?></td>
            <td><?= htmlspecialchars($router['group_name'] ?? 'None') ?></td>
            <td>
                <form action="groups.php" method="post" style="display:inline;">
                    <input type="hidden" name="router_id" value="<?= $router['id'] ?>">
                    <select name="group_id">
                        <option value="">None</option>
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>" <?= ($router['group_id'] == $group['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($group['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" name="assign_router" value="Assign">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
