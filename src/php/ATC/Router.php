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
    public function __construct($requestURI) {
        $this->parseRequest($requestURI);
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
     *
     * @param string $requestURI
     */
    protected function parseRequest($requestURI) {
        $this->reqURI = $requestURI;
        $this->uri = parse_url($this->reqURI);

        $this->path = explode('/', trim($this->uri['path'], '/'));

        // get action based on last element of the path
        $lastPart = end($this->path);
        $this->action = $lastPart != '' ? $lastPart : 'index';
        reset($this->path);
    }

    /**
     * Find a template file based on the parsed request path
     */
    protected function findTemplate() {
        $dir = APPLICATION_PATH . '/views/';

        if (implode('/', $this->path) == 'admin-logout') {
            $this->template = $dir . 'index.html';
        } else if (file_exists($dir . implode('/', $this->path) . '/index.html')) {
            $this->template = rtrim($dir . implode('/', $this->path), '/') . '/index.html';
            $this->tokenGroup = trim(implode('/', $this->path) . '/index', '/');
        } else if (file_exists($dir . implode('/', $this->path) . '.html')) {
            $this->template = $dir . implode('/', $this->path) . '.html';
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

        $dir = APPLICATION_PATH . '/controllers/';
        foreach ($parts as $part) {
            if (file_exists($dir . $part . '.php')) {
                $this->controller = $dir . $part . '.php';
                $this->controllerClassName = $className . Utilities::formatClassName($part);
                return;
            }

            if (!is_dir($dir . $part . '/')) {
                break;
            }

            $dir .= $part . '/';

            if (file_exists($dir . 'Index.php')) {
                $this->controller = $dir . 'Index.php';
                $this->controllerClassName = $className . 'Index';
                return;
            }
        }

        if (file_exists($dir . 'Index.php')) {
            $this->controller = $dir . 'Index.php';
            $this->controllerClassName = $className . 'Index';
        }
    }
}