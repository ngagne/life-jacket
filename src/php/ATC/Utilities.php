<?php

namespace ATC;

/**
 * Class Utilities
 * @package ATC
 */
class Utilities
{
    /**
     * Format route string as a PHP class name
     *
     * @param string $value
     * @return string
     */
    static public function formatClassName($value) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $value)));
    }

    /**
     * Format route as an action name
     *
     * @param string $string
     * @return string
     */
    static public function formatActionName($string) {

        $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
        $str[0] = strtolower($str[0]);

        return $str;
    }
}