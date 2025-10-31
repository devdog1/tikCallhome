<?php
require_once('../../config.php');
include('header.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $unadopted_routers = $pdo->query("SELECT * FROM routers WHERE adopted = false ORDER BY last_seen DESC")->fetchAll(PDO::FETCH_ASSOC);
    $commands = $pdo->query("SELECT * FROM commands ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Pending Adoption</h2>
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Model</th>
                    <th>IP Address</th>
                    <th>Last Seen</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unadopted_routers as $router): ?>
                <tr>
                    <td><?= htmlspecialchars($router['serial_number']) ?></td>
                    <td><?= htmlspecialchars($router['model']) ?></td>
                    <td><?= htmlspecialchars($router['ip_address']) ?></td>
                    <td><?= htmlspecialchars($router['last_seen']) ?></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm preview-btn" data-router-id="<?= $router['id'] ?>">Preview Config</button>
                        <form action="adopt_router.php" method="post" style="display:inline;">
                            <input type="hidden" name="router_id" value="<?= $router['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">Adopt</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <h2>Add New Command</h2>
        <form action="add_command.php" method="post">
            <div class="form-group">
                <label for="command">Command:</label>
                <textarea id="command" name="command" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="check_command">Check Command:</label>
                <textarea id="check_command" name="check_command" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" class="form-control">
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type" class="form-control">
                    <option value="generic">Generic</option>
                    <option value="group">Group</option>
                    <option value="model">Model</option>
                    <option value="serial">Serial</option>
                </select>
            </div>
            <div class="form-group">
                <label for="target">Target (Group ID, Model, or Serial):</label>
                <input type="text" id="target" name="target" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Add Command</button>
        </form>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h2>Existing Commands</h2>
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Command</th>
                    <th>Check Command</th>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commands as $command): ?>
                <tr>
                    <td><?= htmlspecialchars($command['id']) ?></td>
                    <td><?= htmlspecialchars($command['command']) ?></td>
                    <td><?= htmlspecialchars($command['check_command']) ?></td>
                    <td><?= htmlspecialchars($command['description']) ?></td>
                    <td><?= htmlspecialchars($command['type']) ?></td>
                    <td><?= htmlspecialchars($command['target']) ?></td>
                    <td>
                        <a href="edit_command.php?id=<?= $command['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_command.php?id=<?= $command['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
