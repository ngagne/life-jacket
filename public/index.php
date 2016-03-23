<?php
// define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// error reporting
if (APPLICATION_ENV == 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// autoload libraries
require dirname(__FILE__) . '/../src/php/autoload.php';

// initialize application
try {
    $core = new ATC\Core();
} catch(Exception $e) {
    // handle exceptions
    die(str_replace('[[__errors]]', (ini_get('display_errors') ? $e->getMessage() : ''), file_get_contents(APPLICATION_PATH . '/views/error.html')));
}