<?php
require_once('../../config.php');
include('header.php');

try {
    $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("UPDATE groups SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $_POST['group_name'], 'id' => $_POST['id']]);
        header("Location: groups.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2>Edit Group</h2>
<form action="edit_group.php" method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($group['id']) ?>">
    <div class="form-group">
        <label for="group_name">Group Name:</label>
        <input type="text" id="group_name" name="group_name" class="form-control" value="<?= htmlspecialchars($group['name']) ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Group</button>
</form>

<?php include('footer.php'); ?>
