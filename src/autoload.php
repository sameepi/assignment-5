<?php

spl_autoload_register(function ($class) {
    // Debug: Log the class being loaded
    error_log("Attempting to load class: $class");
    
    // Project-specific namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/';
    
    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        error_log("Class $class does not use prefix $prefix");
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    error_log("Looking for class file: $file");
    
    // If the file exists, require it
    if (file_exists($file)) {
        error_log("Including file: $file");
        require $file;
        
        // Check if the class exists after including the file
        if (!class_exists($class, false) && !interface_exists($class, false) && !trait_exists($class, false)) {
            error_log("Class $class not found in file: $file");
        }
    } else {
        error_log("File not found: $file");
    }
});
