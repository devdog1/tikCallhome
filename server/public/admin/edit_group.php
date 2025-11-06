<?php
require_once('../../database.php');
require_once('../../helpers.php');
include('header.php');

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $groupId = $_POST['id'];
        $newTemplateId = $_POST['base_template_id'] ?: null;

        // Get the old group details
        $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = :id");
        $stmt->execute(['id' => $groupId]);
        $oldGroup = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update the group
        $stmt = $pdo->prepare("UPDATE groups SET name = :name, base_template_id = :template_id WHERE id = :id");
        $stmt->execute(['name' => $_POST['group_name'], 'template_id' => $newTemplateId, 'id' => $groupId]);

        log_user_action($pdo, $_SESSION['user_id'], null, "Group #" . $groupId . " updated. Name: " . $_POST['group_name'] . ", Template ID: " . $newTemplateId);

        // If the template has changed, reset the execution history for the old template
        if ($oldGroup['base_template_id'] != $newTemplateId && $oldGroup['base_template_id'] != null) {
            // Find the command associated with the old template
            $stmt = $pdo->prepare("SELECT c.id FROM commands c JOIN config_templates t ON c.command = t.content WHERE t.id = :template_id");
            $stmt->execute(['template_id' => $oldGroup['base_template_id']]);
            $command = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($command) {
                // Find all routers in the group
                $stmt = $pdo->prepare("SELECT id FROM routers WHERE group_id = :group_id");
                $stmt->execute(['group_id' => $groupId]);
                $routers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($routers as $router) {
                    // Delete the execution records for this command and router
                    $stmt = $pdo->prepare("DELETE FROM router_commands WHERE command_id = :command_id AND router_id = :router_id");
                    $stmt->execute(['command_id' => $command['id'], 'router_id' => $router['id']]);
                }
            }
        }

        header("Location: groups.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM groups WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    $templates = $pdo->query("SELECT * FROM config_templates ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

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
    <div class="form-group">
        <label for="base_template_id">Base Config Template:</label>
        <select id="base_template_id" name="base_template_id" class="form-control">
            <option value="">None</option>
            <?php foreach ($templates as $template): ?>
                <option value="<?= $template['id'] ?>" <?= ($group['base_template_id'] == $template['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($template['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Update Group</button>
</form>

<?php include('footer.php'); ?>
