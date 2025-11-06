<?php
session_start();
require_once('../../database.php');
include('header.php');

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_wifi_config'])) {
            $stmt = $pdo->prepare("INSERT INTO wifi_configs (name, ssid, security_protocol, password) VALUES (:name, :ssid, :security, :password)");
            $stmt->execute(['name' => $_POST['name'], 'ssid' => $_POST['ssid'], 'security' => $_POST['security'], 'password' => $_POST['password']]);
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

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success_message'] ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error_message'] ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <h2>Create New WiFi Configuration</h2>
        <form action="wifi.php" method="post">
            <div class="form-group">
                <label for="name">Config Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="ssid">SSID:</label>
                <input type="text" id="ssid" name="ssid" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="security">Security Protocol:</label>
                <select id="security" name="security" class="form-control">
                    <option value="wpa2-psk">WPA2 PSK</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="add_wifi_config" class="btn btn-primary">Create Config</button>
        </form>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h2>Existing WiFi Configurations</h2>
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>SSID</th>
                    <th>Security</th>
                    <th>Actions</th>
                    <th>Apply to Group</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($wifi_configs as $config): ?>
                <tr>
                    <td><?= htmlspecialchars($config['name']) ?></td>
                    <td><?= htmlspecialchars($config['ssid']) ?></td>
                    <td><?= htmlspecialchars($config['security_protocol']) ?></td>
                    <td>
                        <a href="edit_wifi.php?id=<?= $config['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_wifi.php?id=<?= $config['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                    <td>
                        <form action="wifi.php" method="post" class="form-inline">
                            <input type="hidden" name="wifi_config_id" value="<?= $config['id'] ?>">
                            <div class="form-group">
                                <select name="group_id" class="form-control">
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="apply_to_group" class="btn btn-primary btn-sm ml-2">Apply</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
