<?php

namespace ATC;

/**
 * Class Core
 * @package ATC
 */
class Core
{
    protected $router;
    protected $controller;
    protected $stringsHandler;
    protected $view;

    /**
     * Core constructor.
     */
    public function __construct() {
        $this->start();
    }

    /**
     * Start the application
     *
     * @throws \Exception
     */
    protected function start() {
        $config = Config::getInstance();

        // check for cached output
        if ($config->cache_enabled && $_SERVER['REQUEST_METHOD'] == 'GET') {
            $cache = Cache::getInstance();
            $html = $cache->fetch('controller:render:' . $_SERVER['REQUEST_URI']);
            if (!empty($html)) {
                echo $html;
                echo "\n".'<!--from cache-->';
                die();
            }
        }

        // route request
        $this->router = new Router();

        // handle templates based on parsed route
        $this->stringsHandler = new StringsHandler();
        $this->view = new View($this->router, $this->stringsHandler);

        // determine controller action based on parsed route
        $actionName = Utilities::formatActionName($this->router->action) . 'Action';

        // load controller based on parsed route
        if (!empty($this->router->controller)) {
            if (!file_exists($this->router->controller)) {
                throw new \Exception('Controller was not found: ' . $this->router->controller);
            }

            require $this->router->controller;
            $this->controller = new $this->router->controllerClassName($this->router, $this->view);
        } else {
            $this->controller = new Controller($this->router, $this->view);
        }

        // check if controller contains the requested action
        if (array_search($actionName, get_class_methods($this->controller)) === false) {
            // set default action
            $actionName = 'indexAction';
        }

        // process request through controller
        $this->controller->preProcess();
        $this->controller->$actionName();

        // output html
        $this->controller->render();
    }
}