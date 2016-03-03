<?php

namespace ATC;

/**
 * Class Router
 * @package ATC
 */
class Router
{
    public $reqURI;
    public $uri;
    protected $path;
    public $controller;
    public $controllerClassName;
    public $action;
    public $template = '';
    public $tokenGroup = '';

    /**
     * Router constructor.
     */
    public function __construct() {
        $this->parseRequest();
        $this->findTemplate();
        $this->findController();
    }

    /**
     * Send HTTP redirect to browser
     *
     * @param string $path
     */
    public function redirect($path) {
        $config = Config::getInstance();
        header('Location: ' . rtrim($config->get('site_root', '/'), '/') . $path);
        die();
    }

    /**
     * Parse requested URL
     */
    protected function parseRequest() {
        $this->reqURI = $_SERVER['REQUEST_URI'];
        $this->uri = parse_url($_SERVER['REQUEST_URI']);

        $this->path = explode('/', trim($this->uri['path'], '/'));

        if (count($this->path) == 1) {
            $this->action = $this->path[0] != '' ? $this->path[0] : 'index';
        } else {
            $this->action = $this->path[1] != '' ? $this->path[1] : 'index';
        }
    }

    /**
     * Find a template file based on the parsed request path
     */
    protected function findTemplate() {
        $file = APPLICATION_PATH . '/views/';

        if (implode('/', $this->path) == 'admin-logout') {
            $this->template = $file . '/index.html';
        } else if (file_exists($file . implode('/', $this->path) . '/index.html')) {
            $this->template = $file . implode('/', $this->path) . '/index.html';
            $this->tokenGroup = trim(implode('/', $this->path) . '/index', '/');
        } else if (file_exists($file . implode('/', $this->path) . '.html')) {
            $this->template = $file . implode('/', $this->path) . '.html';
            $this->tokenGroup = implode('/', $this->path);
        }
    }

    /**
     * Find a controller based on the parsed request path
     */
    protected function findController() {
        $parts = $this->path;
        $className = '\\Controllers\\';
        array_pop($parts);

        $file = APPLICATION_PATH . '/controllers/';
        foreach ($parts as $part) {
            if (file_exists($file . $part . '.php')) {
                $this->controller = $file . $part . '.php';
                $this->controllerClassName = $className . Utilities::formatClassName($part);
                return;
            }

            if (!is_dir($file . $part . '/')) {
                break;
            }

            $file .= $part . '/';

            if (file_exists($file . 'Index.php')) {
                $this->controller = $file . 'Index.php';
                $this->controllerClassName = $className . 'Index';
                return;
            }
        }

        if (file_exists($file . 'Index.php')) {
            $this->controller = $file . 'Index.php';
            $this->controllerClassName = $className . 'Index';
        }
    }
}