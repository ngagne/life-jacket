<?php

namespace ATC;

class Session
{
    private static $instance;

    protected function __construct() {}
    private function __clone() {}

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();

            session_start();
        }

        return static::$instance;
    }

    public function get($key, $default = '') {
        if (!isset($_SESSION[$key])) {
            return $default;
        }

        return $_SESSION[$key];
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
}