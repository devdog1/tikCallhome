<?php
require_once('../../config.php');

if (isset($_GET['script'])) {
    $script_name = $_GET['script'];
    $script_path = __DIR__ . '/../../../mikrotik/' . $script_name;

    if (file_exists($script_path)) {
        $script_content = file_get_contents($script_path);

        // Replace placeholders with actual URLs from config
        $script_content = str_replace('%%ADOPT_SCRIPT_URL%%', $adoptScriptUrl, $script_content);
        $script_content = str_replace('%%PULL_SCRIPT_URL%%', $pullScriptUrl, $script_content);

        // Serve the script as a download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $script_name . '"');
        echo $script_content;
    } else {
        http_response_code(404);
        echo "Script not found.";
    }
} else {
    http_response_code(400);
    echo "No script specified.";
}
