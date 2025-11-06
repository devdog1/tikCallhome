<?php
session_start();
require_once('../../database.php');
include('header.php');

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_group'])) {
            $stmt = $pdo->prepare("INSERT INTO groups (name) VALUES (:name)");
            $stmt->execute(['name' => $_POST['group_name']]);
        } elseif (isset($_POST['assign_router'])) {
            $stmt = $pdo->prepare("UPDATE routers SET group_id = :group_id WHERE id = :router_id");
            $stmt->execute(['group_id' => $_POST['group_id'] ?: null, 'router_id' => $_POST['router_id']]);
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
        <h2>Create New Group</h2>
        <form action="groups.php" method="post">
            <div class="form-group">
                <label for="group_name">Group Name:</label>
                <input type="text" id="group_name" name="group_name" class="form-control" required>
            </div>
            <button type="submit" name="add_group" class="btn btn-primary">Create Group</button>
        </form>

        <h2 class="mt-4">Existing Groups</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $group): ?>
                <tr>
                    <td><?= htmlspecialchars($group['name']) ?></td>
                    <td>
                        <a href="edit_group.php?id=<?= $group['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_group.php?id=<?= $group['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h2>Assign Routers to Groups</h2>
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Model</th>
                    <th>API Key</th>
                    <th>Current Group</th>
                    <th>Execution Method</th>
                    <th>Assign to Group</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($routers as $router): ?>
                <tr>
                    <td><?= htmlspecialchars($router['serial_number']) ?></td>
                    <td><?= htmlspecialchars($router['model']) ?></td>
                    <td><code><?= htmlspecialchars($router['api_key']) ?></code></td>
                    <td><?= htmlspecialchars($router['group_name'] ?? 'None') ?></td>
                    <td>
                        Pull
                    </td>
                    <td>
                        <form action="groups.php" method="post" class="form-inline">
                            <input type="hidden" name="router_id" value="<?= $router['id'] ?>">
                            <div class="form-group">
                                <select name="group_id" class="form-control">
                                    <option value="">None</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>" <?= ($router['group_id'] == $group['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($group['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="assign_router" class="btn btn-primary btn-sm ml-2">Assign</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
