<?php
require_once('../../database.php');
include('header.php');

try {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $templateId = $_POST['id'];
        $newContent = $_POST['content'];
        $workflowType = $_POST['workflow_type'];

        // Get the old template content for comparison
        $stmt = $pdo->prepare("SELECT * FROM config_templates WHERE id = :id");
        $stmt->execute(['id' => $templateId]);
        $oldTemplate = $stmt->fetch(PDO::FETCH_ASSOC);

        // Update the template
        $stmt = $pdo->prepare("UPDATE config_templates SET name = :name, description = :desc, content = :content, workflow_type = :type WHERE id = :id");
        $stmt->execute(['name' => $_POST['name'], 'desc' => $_POST['description'], 'content' => $newContent, 'type' => $workflowType, 'id' => $templateId]);

        // If it's a 'complete' template and the content has changed, reset the execution history
        if ($workflowType == 'complete' && $oldTemplate['content'] != $newContent) {
            // Find the command associated with this template
            $stmt = $pdo->prepare("SELECT id FROM commands WHERE command = :content");
            $stmt->execute(['content' => $oldTemplate['content']]);
            $command = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($command) {
                // Delete the execution records for this command
                $stmt = $pdo->prepare("DELETE FROM router_commands WHERE command_id = :command_id");
                $stmt->execute(['command_id' => $command['id']]);
            }
        }

        header("Location: templates.php");
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM config_templates WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $template = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<h2>Edit Config Template</h2>
<form action="edit_template.php" method="post">
    <input type="hidden" name="id" value="<?= htmlspecialchars($template['id']) ?>">
    <div class="form-group">
        <label for="name">Template Name:</label>
        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($template['name']) ?>" required>
    </div>
    <div class="form-group">
        <label for="description">Description:</label>
        <input type="text" id="description" name="description" class="form-control" value="<?= htmlspecialchars($template['description']) ?>">
    </div>
    <div class="form-group">
        <label for="content">Script Content:</label>
        <textarea id="content" name="content" class="form-control" rows="10" required><?= htmlspecialchars($template['content']) ?></textarea>
    </div>
    <div class="form-group">
        <label for="workflow_type">Workflow Type:</label>
        <select id="workflow_type" name="workflow_type" class="form-control">
            <option value="incremental" <?= ($template['workflow_type'] == 'incremental') ? 'selected' : '' ?>>Incremental</option>
            <option value="complete" <?= ($template['workflow_type'] == 'complete') ? 'selected' : '' ?>>Complete</option>
        </select>
        <small class="form-text text-muted">'Complete' templates should handle their own cleanup.</small>
    </div>
    <button type="submit" class="btn btn-primary">Update Template</button>
</form>

<?php include('footer.php'); ?>
