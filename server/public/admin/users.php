<?php
require_once 'header.php';
require_once 'check_permission.php';

check_permission('manage_users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        check_permission('manage_users');
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role_id = $_POST['role_id'];
        $user_handler->createUser($username, $password, $role_id);
    } elseif (isset($_POST['update_user'])) {
        check_permission('manage_users');
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role_id = $_POST['role_id'];
        $user_handler->updateUser($id, $username, $role_id);
    } elseif (isset($_POST['delete_user'])) {
        check_permission('manage_users');
        $id = $_POST['id'];
        $user_handler->deleteUser($id);
    }
}

$roles = $pdo->query('SELECT * FROM user_roles')->fetchAll();
$users = $pdo->query('SELECT users.*, user_roles.name as role_name FROM users JOIN user_roles ON users.role_id = user_roles.id')->fetchAll();
?>

<h2>User Management</h2>

<?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
<div class="card">
    <div class="card-header">
        Add User
    </div>
    <div class="card-body">
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role_id">Role</label>
                <select name="role_id" id="role_id" class="form-control" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header">
        Users
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="post">
                            <td>
                                <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                                    <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>">
                                <?php else: ?>
                                    <?php echo $user['username']; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                                    <select name="role_id" class="form-control">
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $user['role_id'] ? 'selected' : ''; ?>>
                                                <?php echo $role['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <?php echo $user['role_name']; ?>
                                <?php endif; ?>
                            </td>
                            <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_users')): ?>
                                <td>
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="update_user" class="btn btn-primary">Update</button>
                                    <button type="submit" name="delete_user" class="btn btn-danger">Delete</button>
                                </td>
                            <?php endif; ?>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
