<?php
// autoload.php
spl_autoload_register(function ($class_name) {
    // List of directories to search for classes
    $directories = [
        'classes/',
        'includes/',
        'processes/'
    ];

    foreach ($directories as $directory) {
        $file = __DIR__ . '/' . $directory . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    // If class not found, throw an exception
    throw new Exception("Class '{$class_name}' not found");
});

// Include config file
require_once 'config.php';
