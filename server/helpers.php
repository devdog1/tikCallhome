<?php

/**
 * Obfuscates passwords in a command string for safe display.
 * Replaces password="somepass" or password=somepass with password="*****".
 *
 * @param string $command The command string.
 * @return string The obfuscated command string.
 */
function obfuscate_password($command) {
    if (empty($command)) {
        return '';
    }

    // This regex looks for 'password=' followed by either a quoted string or an unquoted string.
    $pattern = '/(password=)(?:"([^"]*)"|([^\s]+))/i';

    return preg_replace_callback($pattern, function($matches) {
        // $matches[1] will be 'password='
        // $matches[2] will be the content of a quoted password (if it exists)
        // $matches[3] will be the unquoted password (if it exists)

        if (isset($matches[2]) && $matches[2] !== '') {
            // The password was quoted, so keep the quotes in the replacement.
            return $matches[1] . '"*****"';
        } else {
            // The password was not quoted.
            return $matches[1] . '*****';
        }
    }, $command);
}

/**
 * Logs a user action to the database.
 *
 * @param PDO $pdo The database connection object.
 * @param int|null $userId The ID of the user performing the action. Can be null for system actions.
 * @param int|null $routerId The ID of the router being affected. Can be null if the action is not router-specific.
 * @param string $action The description of the action being performed.
 * @param string|null $details Additional details about the action, such as the command that was run.
 */
function log_user_action($pdo, $userId, $routerId, $action, $details = null) {
    $stmt = $pdo->prepare("
        INSERT INTO user_actions (user_id, router_id, action, details)
        VALUES (:user_id, :router_id, :action, :details)
    ");
    $stmt->bindParam(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':router_id', $routerId, $routerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
    $stmt->bindParam(':details', $details, $details === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->execute();
}
