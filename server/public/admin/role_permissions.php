<?php
require_once 'header.php';
require_once 'check_permission.php';

check_permission('manage_users'); // Only admins can manage permissions

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start a transaction
    $pdo->beginTransaction();
    try {
        // First, clear all existing permissions to rebuild them from the form.
        $pdo->exec('DELETE FROM role_permissions');

        // Loop through the submitted POST data
        if (isset($_POST['permissions'])) {
            foreach ($_POST['permissions'] as $role_id => $permission_ids) {
                foreach ($permission_ids as $permission_id) {
                    $stmt = $pdo->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)');
                    $stmt->execute([$role_id, $permission_id]);
                }
            }
        }
        // Commit the transaction
        $pdo->commit();
        $success_message = "Permissions updated successfully!";
    } catch (Exception $e) {
        // Roll back the transaction if something failed
        $pdo->rollBack();
        $error_message = "An error occurred: " . $e->getMessage();
    }
}

// Fetch all roles and permissions from the database
$roles = $pdo->query('SELECT * FROM user_roles ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$permissions = $pdo->query('SELECT * FROM permissions ORDER BY permission_key')->fetchAll(PDO::FETCH_ASSOC);

// Fetch the current role-permission associations
$role_permissions_raw = $pdo->query('SELECT * FROM role_permissions')->fetchAll(PDO::FETCH_ASSOC);
$role_permissions = [];
foreach ($role_permissions_raw as $rp) {
    $role_permissions[$rp['role_id']][$rp['permission_id']] = true;
}

?>

<h2>Manage Role Permissions</h2>
<p>Use the matrix below to assign permissions to roles. Changes will apply to all users assigned to that role.</p>

<?php if (isset($success_message)): ?>
    <div class="alert alert-success"><?= $success_message ?></div>
<?php elseif (isset($error_message)): ?>
    <div class="alert alert-danger"><?= $error_message ?></div>
<?php endif; ?>

<form method="post">
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Permission</th>
                    <?php foreach ($roles as $role): ?>
                        <th class="text-center"><?= htmlspecialchars($role['name']) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $permission): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($permission['permission_key']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($permission['description']) ?></small>
                        </td>
                        <?php foreach ($roles as $role): ?>
                            <td class="text-center">
                                <input type="checkbox" name="permissions[<?= $role['id'] ?>][]" value="<?= $permission['id'] ?>"
                                    <?php if (isset($role_permissions[$role['id']][$permission['id']])): ?>checked<?php endif; ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <button type="submit" class="btn btn-primary">Save Permissions</button>
</form>

<?php require_once 'footer.php'; ?>
