<?php
// Include the configuration file
require_once('../../config.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $unadopted_routers = $pdo->query("SELECT * FROM routers WHERE adopted = false ORDER BY last_seen DESC")->fetchAll(PDO::FETCH_ASSOC);
    $commands = $pdo->query("SELECT * FROM commands ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mikrotik Management</title>
</head>
<body>
    <h1>Mikrotik Management</h1>
    <hr>
    <h2><a href="index.php">Routers & Commands</a> | <a href="groups.php">Groups</a> | <a href="wifi.php">WiFi Configs</a></h2>
    <hr>

    <h2>Pending Adoption</h2>
    <table border="1">
        <tr>
            <th>Serial Number</th>
            <th>Model</th>
            <th>IP Address</th>
            <th>Last Seen</th>
            <th>Action</th>
        </tr>
        <?php foreach ($unadopted_routers as $router): ?>
        <tr>
            <td><?= htmlspecialchars($router['serial_number']) ?></td>
            <td><?= htmlspecialchars($router['model']) ?></td>
            <td><?= htmlspecialchars($router['ip_address']) ?></td>
            <td><?= htmlspecialchars($router['last_seen']) ?></td>
            <td>
                <form action="adopt_router.php" method="post" style="display:inline;">
                    <input type="hidden" name="router_id" value="<?= $router['id'] ?>">
                    <input type="submit" value="Adopt">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Add New Command</h2>
    <form action="add_command.php" method="post">
        <label for="command">Command:</label><br>
        <textarea id="command" name="command" rows="4" cols="50" required></textarea><br>
        <label for="check_command">Check Command (should return empty if command needs to be run):</label><br>
        <textarea id="check_command" name="check_command" rows="2" cols="50"></textarea><br>
        <label for="description">Description:</label><br>
        <input type="text" id="description" name="description"><br>
        <label for="type">Type:</label><br>
        <select id="type" name="type">
            <option value="generic">Generic</option>
            <option value="group">Group</option>
            <option value="model">Model</option>
            <option value="serial">Serial</option>
        </select><br>
        <label for="target">Target (Group ID, Model, or Serial):</label><br>
        <input type="text" id="target" name="target"><br><br>
        <input type="submit" value="Add Command">
    </form>
    <h2>Existing Commands</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Command</th>
            <th>Check Command</th>
            <th>Description</th>
            <th>Type</th>
            <th>Target</th>
        </tr>
        <?php foreach ($commands as $command): ?>
        <tr>
            <td><?= htmlspecialchars($command['id']) ?></td>
            <td><?= htmlspecialchars($command['command']) ?></td>
            <td><?= htmlspecialchars($command['check_command']) ?></td>
            <td><?= htmlspecialchars($command['description']) ?></td>
            <td><?= htmlspecialchars($command['type']) ?></td>
            <td><?= htmlspecialchars($command['target']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
