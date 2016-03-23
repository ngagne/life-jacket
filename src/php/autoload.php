<?php
// define path to public directory
defined('PUBLIC_PATH') || define('PUBLIC_PATH', realpath(dirname(__FILE__) . '/../../public/'));

// define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../' . 'app'));

// load SwiftMailer library
$file = realpath(APPLICATION_PATH . '/../') . '/bower_components/swiftmailer/lib/swift_required.php';
if (!file_exists($file)) {
    die('The SwiftMailer library was not found: ' . $file);
}
require $file;

// autoload ATC libraries
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'ATC\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . DIRECTORY_SEPARATOR . 'ATC' . DIRECTORY_SEPARATOR;

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});