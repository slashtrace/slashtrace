<?php

$autoload_paths = [
    __DIR__ . "/../vendor/autoload.php",
    __DIR__ . "/../../../autoload.php",
];

foreach ($autoload_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        return;
    }
}
