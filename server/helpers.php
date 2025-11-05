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
