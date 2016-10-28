<?php

class Translate {
    public static function t($string) {
        global $config;

        return isset($config['translations'][$string])
            ? $config['translations'][$string]
            : $string;
    }
}
