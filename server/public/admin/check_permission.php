<?php
function check_permission($permission) {
    global $user_handler;
    if (!$user_handler->hasPermission($_SESSION['user_id'], $permission)) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1>';
        exit();
    }
}
