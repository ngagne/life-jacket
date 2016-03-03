<?php

namespace ATC;

class Session
{
    private static $instance;

    protected function __construct() {}
    private function __clone() {}

    /**
     * Get a singleton instance
     *
     * @return Session
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();

            session_start();
        }

        return static::$instance;
    }

    /**
     * Get value from session
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function get($key, $default = '') {
        if (!isset($_SESSION[$key])) {
            return $default;
        }

        return $_SESSION[$key];
    }

    /**
     * Store value in session
     *
     * @param string $key
     * @param string $value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
}