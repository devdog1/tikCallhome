<?php
require_once('../../database.php');
include('header.php');

try {
    // Fetch adopted routers
    $adopted_routers = $pdo->query("SELECT * FROM routers WHERE adopted = true ORDER BY last_seen DESC")->fetchAll(PDO::FETCH_ASSOC);
    // Fetch unadopted routers
    $unadopted_routers = $pdo->query("SELECT * FROM routers WHERE adopted = false ORDER BY last_seen DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Adopted Routers</h2>
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Serial Number</th>
                    <th>Model</th>
                    <th>IP Address</th>
                    <th>Last Seen</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adopted_routers as $router): ?>
                <tr>
                    <td><?= htmlspecialchars($router['serial_number']) ?></td>
                    <td><?= htmlspecialchars($router['model']) ?></td>
                    <td><?= htmlspecialchars($router['ip_address']) ?></td>
                    <td><?= htmlspecialchars($router['last_seen']) ?></td>
                    <td>
                        <a href="edit_router.php?id=<?= $router['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="command_log.php?router_id=<?= $router['id'] ?>" class="btn btn-info btn-sm">View Log</a>
                        <a href="delete_router.php?id=<?= $router['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-4">
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

<?php include('footer.php'); ?>
