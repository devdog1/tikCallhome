<?php
// Include the configuration file
require_once('../../config.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_wifi_config'])) {
            $stmt = $pdo->prepare("INSERT INTO wifi_configs (name, ssid, security_protocol, password) VALUES (:name, :ssid, :security, :password)");
            $stmt->execute([
                'name' => $_POST['name'],
                'ssid' => $_POST['ssid'],
                'security' => $_POST['security'],
                'password' => $_POST['password']
            ]);
        } elseif (isset($_POST['apply_to_group'])) {
            $wifiConfigId = $_POST['wifi_config_id'];
            $groupId = $_POST['group_id'];

            // Fetch the WiFi config
            $stmt = $pdo->prepare("SELECT * FROM wifi_configs WHERE id = :id");
            $stmt->execute(['id' => $wifiConfigId]);
            $wifiConfig = $stmt->fetch(PDO::FETCH_ASSOC);

            // Generate the command
            $command = "/interface wireless security-profiles set [find default=yes] mode=dynamic-keys authentication-types=wpa2-psk wpa2-pre-shared-key=\"{$wifiConfig['password']}\"; /interface wireless set [find default-name=wlan1] ssid=\"{$wifiConfig['ssid']}\" security-profile=[find default=yes]";
            $check_command = "/interface wireless get [find default-name=wlan1] ssid"; // Simple check

            // Create the command for the group
            $stmt = $pdo->prepare("INSERT INTO commands (command, check_command, description, type, target) VALUES (:command, :check_command, :description, 'group', :target)");
            $stmt->execute([
                'command' => $command,
                'check_command' => $check_command,
                'description' => "WiFi config: {$wifiConfig['name']}",
                'target' => $groupId
            ]);
        }
        header("Location: wifi.php");
        exit;
    }

    $wifi_configs = $pdo->query("SELECT * FROM wifi_configs ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $groups = $pdo->query("SELECT * FROM groups ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>WiFi Management</title>
</head>
<body>
    <h1>Mikrotik Management</h1>
    <hr>
    <h2><a href="index.php">Routers & Commands</a> | <a href="groups.php">Groups</a> | <a href="wifi.php">WiFi Configs</a></h2>
    <hr>

    <h2>Create New WiFi Configuration</h2>
    <form action="wifi.php" method="post">
        <label for="name">Config Name:</label>
        <input type="text" id="name" name="name" required><br>
        <label for="ssid">SSID:</label>
        <input type="text" id="ssid" name="ssid" required><br>
        <label for="security">Security Protocol:</label>
        <select id="security" name="security">
            <option value="wpa2-psk">WPA2 PSK</option>
        </select><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <input type="submit" name="add_wifi_config" value="Create Config">
    </form>

    <h2>Existing WiFi Configurations</h2>
    <table border="1">
        <tr>
            <th>Name</th>
            <th>SSID</th>
            <th>Security</th>
            <th>Apply to Group</th>
        </tr>
        <?php foreach ($wifi_configs as $config): ?>
        <tr>
            <td><?= htmlspecialchars($config['name']) ?></td>
            <td><?= htmlspecialchars($config['ssid']) ?></td>
            <td><?= htmlspecialchars($config['security_protocol']) ?></td>
            <td>
                <form action="wifi.php" method="post" style="display:inline;">
                    <input type="hidden" name="wifi_config_id" value="<?= $config['id'] ?>">
                    <select name="group_id">
                        <?php foreach ($groups as $group): ?>
                            <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" name="apply_to_group" value="Apply">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
