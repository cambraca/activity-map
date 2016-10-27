<?php

class Autoloader {
    static function autoload($class) {
        $parts = explode('\\', $class);
        $filename = 'src' . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';

        if (file_exists($filename)) {
            include $filename;
        }
    }
}

spl_autoload_register(array('Autoloader', 'autoload'));
