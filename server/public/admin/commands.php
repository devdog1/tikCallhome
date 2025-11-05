<?php
require_once('../../database.php');
require_once('../../helpers.php');
include('header.php');
include('check_permission.php');

check_permission('manage_commands');

try {
    $commands = $pdo->query("SELECT * FROM commands ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    // Fetch data for dropdowns
    $groups = $pdo->query("SELECT id, name FROM groups ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $models = $pdo->query("SELECT DISTINCT model FROM routers ORDER BY model")->fetchAll(PDO::FETCH_ASSOC);
    $serials = $pdo->query("SELECT serial_number FROM routers ORDER BY serial_number")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<div class="row mt-4">
    <div class="col-md-6">
        <h2>Add New Command</h2>
        <form action="add_command.php" method="post">
            <div class="form-group">
                <label for="command">Command:</label>
                <textarea id="command" name="command" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="check_command">Check Command (optional):</label>
                <textarea id="check_command" name="check_command" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <input type="text" id="description" name="description" class="form-control">
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type" class="form-control" onchange="toggleTargetInput()">
                    <option value="generic" selected>Generic</option>
                    <option value="group">Group</option>
                    <option value="model">Model</option>
                    <option value="serial">Serial</option>
                </select>
            </div>

            <!-- Target Dropdowns -->
            <div id="target-group" class="form-group" style="display: none;">
                <label for="target_group">Target Group:</label>
                <select id="target_group" name="target_group" class="form-control">
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']) ?>"><?= htmlspecialchars($group['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="target-model" class="form-group" style="display: none;">
                <label for="target_model">Target Model:</label>
                <select id="target_model" name="target_model" class="form-control">
                    <?php foreach ($models as $model): ?>
                        <option value="<?= htmlspecialchars($model['model']) ?>"><?= htmlspecialchars($model['model']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="target-serial" class="form-group" style="display: none;">
                <label for="target_serial">Target Serial Number:</label>
                <select id="target_serial" name="target_serial" class="form-control">
                    <?php foreach ($serials as $serial): ?>
                        <option value="<?= htmlspecialchars($serial['serial_number']) ?>"><?= htmlspecialchars($serial['serial_number']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" id="target" name="target">

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
                    <td>
                        <pre class="command-view" data-original-command="<?= htmlspecialchars($command['command']) ?>"><?= htmlspecialchars(obfuscate_password($command['command'])) ?></pre>
                        <?php if ($user_handler->hasPermission($_SESSION['user_id'], 'manage_commands')): ?>
                            <button class="btn btn-secondary btn-sm reveal-btn">Reveal</button>
                        <?php endif; ?>
                    </td>
                    <td><pre><?= htmlspecialchars($command['check_command']) ?></pre></td>
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

<script>
function toggleTargetInput() {
    var type = document.getElementById('type').value;
    var targetGroup = document.getElementById('target-group');
    var targetModel = document.getElementById('target-model');
    var targetSerial = document.getElementById('target-serial');
    var targetInput = document.getElementById('target');

    // Hide all
    targetGroup.style.display = 'none';
    targetModel.style.display = 'none';
    targetSerial.style.display = 'none';

    // Show selected
    if (type === 'group') {
        targetGroup.style.display = 'block';
        targetInput.value = document.getElementById('target_group').value;
    } else if (type === 'model') {
        targetModel.style.display = 'block';
        targetInput.value = document.getElementById('target_model').value;
    } else if (type === 'serial') {
        targetSerial.style.display = 'block';
        targetInput.value = document.getElementById('target_serial').value;
    } else {
        targetInput.value = '';
    }
}

// Update hidden target on dropdown change
document.getElementById('target_group').addEventListener('change', function() {
    document.getElementById('target').value = this.value;
});
document.getElementById('target_model').addEventListener('change', function() {
    document.getElementById('target').value = this.value;
});
document.getElementById('target_serial').addEventListener('change', function() {
    document.getElementById('target').value = this.value;
});

// Initial call to set form state on page load
toggleTargetInput();
</script>
