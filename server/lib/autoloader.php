<?php
spl_autoload_register(function ($class) {
    $prefix = 'League\\OAuth2\\Client\\';
    if (strpos($class, $prefix) === 0) {
        $file = __DIR__ . '/oauth2-client/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }

    $prefix = 'Greew\\OAuth2\\Client\\Provider\\';
    if (strpos($class, $prefix) === 0) {
        $file = __DIR__ . '/oauth2-azure/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }
});
