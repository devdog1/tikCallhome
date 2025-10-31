<?php
require_once('../../config.php');
include('header.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("UPDATE wifi_configs SET name = :name, ssid = :ssid, security_protocol = :security, password = :password WHERE id = :id");
        $stmt->execute([
            'name' => $_POST['name'],
            'ssid' => $_POST['ssid'],
            'security' => $_POST['security'],
            'password' => $_POST['password'],
            'id' => $_POST['id']
        ]);
        header("Location: wifi.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM wifi_configs WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2>Edit WiFi Configuration</h2>
<form action="edit_wifi.php" method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($config['id']) ?>">
    <div class="form-group">
        <label for="name">Config Name:</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($config['name']) ?>" required>
    </div>
    <div class="form-group">
        <label for="ssid">SSID:</label>
        <input type="text" id="ssid" name="ssid" class="form-control" value="<?= htmlspecialchars($config['ssid']) ?>" required>
    </div>
    <div class="form-group">
        <label for="security">Security Protocol:</label>
        <select id="security" name="security" class="form-control">
            <option value="wpa2-psk" <?= ($config['security_protocol'] == 'wpa2-psk') ? 'selected' : '' ?>>WPA2 PSK</option>
        </select>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" class="form-control" value="<?= htmlspecialchars($config['password']) ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Config</button>
</form>

<?php include('footer.php'); ?>
