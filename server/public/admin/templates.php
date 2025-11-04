<?php
require_once('../../database.php');
include('header.php');

try {

    // Handle form submissions for CRUD operations
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_template'])) {
            $stmt = $pdo->prepare("INSERT INTO config_templates (name, description, content, workflow_type) VALUES (:name, :desc, :content, :type)");
            $stmt->execute(['name' => $_POST['name'], 'desc' => $_POST['description'], 'content' => $_POST['content'], 'type' => $_POST['workflow_type']]);
        }
        header("Location: templates.php");
        exit;
    }

    $templates = $pdo->query("SELECT * FROM config_templates ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row">
    <div class="col-md-6">
        <h2>Create New Config Template</h2>
        <form action="templates.php" method="post">
            <div class="form-group">
                <label for="name">Template Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" class="form-control">
            </div>
            <div class="form-group">
                <label for="content">Script Content:</label>
                <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
            </div>
            <div class="form-group">
                <label for="workflow_type">Workflow Type:</label>
                <select id="workflow_type" name="workflow_type" class="form-control">
                    <option value="incremental">Incremental</option>
                    <option value="complete">Complete</option>
                </select>
                <small class="form-text text-muted">'Complete' templates should handle their own cleanup.</small>
            </div>
            <button type="submit" name="add_template" class="btn btn-primary">Create Template</button>
        </form>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <h2>Existing Config Templates</h2>
        <table class="table table-striped data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Workflow</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($templates as $template): ?>
                <tr>
                    <td><?= htmlspecialchars($template['name']) ?></td>
                    <td><?= htmlspecialchars($template['description']) ?></td>
                    <td><?= htmlspecialchars($template['workflow_type']) ?></td>
                    <td>
                        <a href="edit_template.php?id=<?= $template['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="delete_template.php?id=<?= $template['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('footer.php'); ?>
