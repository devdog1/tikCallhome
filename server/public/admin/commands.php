<?php
require_once('../../database.php');
require_once('../../helpers.php');
include('header.php');
include('check_permission.php');

check_permission('manage_commands');

try {
    // Fetch data for dropdowns
    $groups = $pdo->query("SELECT id, name FROM groups ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    $models = $pdo->query("SELECT DISTINCT model FROM routers ORDER BY model")->fetchAll(PDO::FETCH_ASSOC);
    $serials = $pdo->query("SELECT serial_number FROM routers ORDER BY serial_number")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Prepare data for JavaScript
$js_data = [
    'groups' => $groups,
    'models' => $models,
    'serials' => $serials
];
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCommandModal">
                Add New Command
            </button>
        </div>
    </div>

    <!-- Add Command Modal -->
    <div class="modal fade" id="addCommandModal" tabindex="-1" role="dialog" aria-labelledby="addCommandModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="add_command.php" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCommandModalLabel">Add New Command</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Form content remains the same -->
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Command</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="form-group">
                <label for="filter-type">Show commands by Type:</label>
                <select id="filter-type" class="form-control">
                    <option value="" selected>None</option>
                    <option value="generic">Generic</option>
                    <option value="group">Group</option>
                    <option value="model">Model</option>
                    <option value="serial">Serial</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="filter-target">and Target:</label>
                <select id="filter-target" class="form-control" disabled>
                    <option value="">Select Type First</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h2>Existing Commands</h2>
            <table class="table table-striped data-table" id="commands-table">
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
                    <!-- Populated by AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
// Pass PHP data to JavaScript
var targetData = <?= json_encode($js_data); ?>;

function toggleTargetInput() {
    var type = document.getElementById('type').value;
    var targetGroup = document.getElementById('target-group');
    var targetModel = document.getElementById('target-model');
    var targetSerial = document.getElementById('target-serial');
    var targetInput = document.getElementById('target');

    targetGroup.style.display = 'none';
    targetModel.style.display = 'none';
    targetSerial.style.display = 'none';

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

document.getElementById('target_group').addEventListener('change', function() {
    document.getElementById('target').value = this.value;
});
document.getElementById('target_model').addEventListener('change', function() {
    document.getElementById('target').value = this.value;
});
document.getElementById('target_serial').addEventListener('change', function() {
    document.getElementById('target').value = this.value;
});

toggleTargetInput();

$(document).ready(function() {
    var commandsTable = $('#commands-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "get_commands.php",
            "type": "POST",
            "data": function(d) {
                d.type = $('#filter-type').val();
                d.target = $('#filter-target').val();
            }
        },
        "columns": [
            { "data": "id" },
            {
                "data": "command",
                "render": function(data, type, row) {
                    let obfuscated = data.replace(/(password=)(?:"([^"]*)"|([^\s]+))/gi, '$1"*****"');
                    return `<pre class="command-view" data-original-command="${escape(data)}">${obfuscated}</pre>
                            <button class="btn btn-secondary btn-sm reveal-btn">Reveal</button>`;
                }
            },
            { "data": "check_command", "render": function(data) { return `<pre>${data}</pre>`; } },
            { "data": "description" },
            { "data": "type" },
            { "data": "target" },
            {
                "data": "id",
                "render": function(data, type, row) {
                    return `<a href="edit_command.php?id=${data}" class="btn btn-primary btn-sm">Edit</a>
                            <a href="delete_command.php?id=${data}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>`;
                }
            }
        ],
        "searching": false, // Disable DataTables native search
        "lengthChange": false, // Disable show entries dropdown
        "pageLength": 10 // Set default page length
    });

    $('#filter-type, #filter-target').on('change', function() {
        var type = $('#filter-type').val();
        var $targetDropdown = $('#filter-target');

        if (type && type !== 'generic') {
            $targetDropdown.prop('disabled', false);
            $targetDropdown.empty().append('<option value="">All</option>');

            let data, valKey, nameKey;
            if (type === 'group') {
                data = targetData.groups;
                valKey = 'id';
                nameKey = 'name';
            } else if (type === 'model') {
                data = targetData.models;
                valKey = 'model';
                nameKey = 'model';
            } else if (type === 'serial') {
                data = targetData.serials;
                valKey = 'serial_number';
                nameKey = 'serial_number';
            }

            data.forEach(function(item) {
                $targetDropdown.append(`<option value="${item[valKey]}">${item[nameKey]}</option>`);
            });
        } else {
            $targetDropdown.prop('disabled', true);
            $targetDropdown.empty().append('<option value="">N/A</option>');
        }

        commandsTable.ajax.reload();
    });

    // Initial table state
    commandsTable.ajax.reload();
});

// Escape function for data attribute
function escape(s) {
    return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

</script>
