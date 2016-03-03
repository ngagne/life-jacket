<?php

namespace ATC;

class TemplateHandler
{
    private static $templates = array();

    protected function __construct() {}
    private function __clone() {}

    /**
     * Get file contents for a specific template file
     *
     * @param string $file
     * @return string
     * @throws \Exception
     */
    public static function getContents($file) {
        if (null === static::$templates) {
            static::$templates = array();
        }

        if (!isset(static::$templates[$file])) {
            if (!file_exists(APPLICATION_PATH . $file)) {
                throw new \Exception('File was not found: ' . APPLICATION_PATH . $file);
            }
            static::$templates[$file] = file_get_contents(APPLICATION_PATH . $file);
        }

        return static::$templates[$file];
    }

    /**
     * Get file contents for a specific template file and replace values
     *
     * @param $file
     * @param array $map
     * @return mixed
     * @throws \Exception
     */
    public static function render($file, $map = array()) {
        $contents = static::getContents($file);
        return str_replace(array_keys($map), $map, $contents);
    }
}