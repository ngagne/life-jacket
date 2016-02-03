<?php
// define path to public directory
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__)));

// define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../' . 'app'));

// define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// error reporting
if (APPLICATION_ENV == 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

// autoload libraries
require APPLICATION_PATH . '/../src/php/autoload.php';

// initialize application
try {
    $core = new ATC\Core();
} catch(Exception $e) {
    // handle exceptions
    die(str_replace('[[__errors]]', (ini_get('display_errors') ? $e->getMessage() : ''), file_get_contents(APPLICATION_PATH . '/views/error.html')));
}