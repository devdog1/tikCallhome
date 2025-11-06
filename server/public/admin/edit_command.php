<?php
require_once('../../database.php');
require_once('../../helpers.php');
include('header.php');

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $pdo->prepare("UPDATE commands SET command = :command, check_command = :check_command, description = :description, type = :type, target = :target WHERE id = :id");
        $stmt->execute([
            'command' => $_POST['command'],
            'check_command' => $_POST['check_command'],
            'description' => $_POST['description'],
            'type' => $_POST['type'],
            'target' => $_POST['target'],
            'id' => $_POST['id']
        ]);

        log_user_action($pdo, $_SESSION['user_id'], null, "Command #" . $_POST['id'] . " updated", $_POST['command']);

        header("Location: index.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM commands WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $command = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2>Edit Command</h2>
<form action="edit_command.php" method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($command['id']) ?>">
    <div class="form-group">
        <label for="command">Command:</label>
        <textarea id="command" name="command" class="form-control" rows="4" required><?= htmlspecialchars($command['command']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="check_command">Check Command:</label>
        <textarea id="check_command" name="check_command" class="form-control" rows="2"><?= htmlspecialchars($command['check_command']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" class="form-control" value="<?= htmlspecialchars($command['description']) ?>">
    </div>
    <div class="form-group">
        <label for="type">Type:</label>
        <select id="type" name="type" class="form-control">
            <option value="generic" <?= ($command['type'] == 'generic') ? 'selected' : '' ?>>Generic</option>
            <option value="group" <?= ($command['type'] == 'group') ? 'selected' : '' ?>>Group</option>
            <option value="model" <?= ($command['type'] == 'model') ? 'selected' : '' ?>>Model</option>
            <option value="serial" <?= ($command['type'] == 'serial') ? 'selected' : '' ?>>Serial</option>
        </select>
    </div>
    <div class="form-group">
        <label for="target">Target (Group ID, Model, or Serial):</label>
        <input type="text" id="target" name="target" class="form-control" value="<?= htmlspecialchars($command['target']) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Update Command</button>
</form>

<?php include('footer.php'); ?>
