<?php
// Database connection details
$dbHost = 'localhost';
$dbName = 'mikrotik_manager';
$dbUser = 'user';
$dbPass = 'password';

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $commands = $pdo->query("SELECT * FROM commands ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Command Management</title>
</head>
<body>
    <h1>Command Management</h1>
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
            <option value="model">Model</option>
            <option value="serial">Serial</option>
        </select><br>
        <label for="target">Target (if model or serial):</label><br>
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
