<?php

namespace ATC;


class View
{
    protected $html;
    protected $router;
    protected $stringsHandler;

    public function __construct(\ATC\Router $router, \ATC\StringsHandler $stringsHandler) {
        $this->router = $router;
        $this->stringsHandler = $stringsHandler;

        $this->process();
    }

    protected function process() {
        $this->loadTemplateFiles();
        $this->processTokens();
    }

    protected function loadTemplateFiles() {
        // get view
        if ($this->router->template == '' || !file_exists($this->router->template)) {
            http_response_code(404);
            throw new \Exception('View was not found for this action: ' . $this->router->action);
        }
        $view = file_get_contents($this->router->template);

        // check for a layout
        preg_match('/\[\[layout=([0-9a-zA-Z_-]+)\]\]/', $view, $matches);

        $file = APPLICATION_PATH . '/layouts/' . (count($matches) ? $matches[1] : 'index') . '.html';
        if (file_exists($file)) {
            $view = preg_replace('/\[\[layout=([0-9a-zA-Z_-]+)\]\]/', '', $view, 1);
            $layout = file_get_contents($file);
        } else {
            throw new \Exception('Layout was not found: ' . $file);
        }

        // splice view into layout
        $this->html = str_replace('[[__content]]', $view, $layout);

        // cleanup
        unset($view);

        // process any includes
        preg_match_all('/\[\[include=([0-9a-zA-Z_-]+)\]\]/', $this->html, $matches);
        if (count($matches)) {
            foreach ($matches[1] as $i => $match) {
                $file = APPLICATION_PATH . '/layouts/_partials/' . $match . '.html';
                if (file_exists($file)) {
                    $this->html = str_replace($matches[0][$i], file_get_contents($file), $this->html);
                } else {
                    throw new \Exception('Include was not found: ' . $file);
                }
            }
        }
    }

    protected function processTokens() {
        $config = Config::getInstance();

        // process any global tokens
        if (!empty($this->stringsHandler->strings['_global'])) {
            foreach ($this->stringsHandler->strings['_global'] as $token => $string) {
                $this->html = str_replace('[[' . $token . ']]', $string, $this->html);
            }
        }

        // process any action specific tokens
        if (isset($this->stringsHandler->strings[$this->router->tokenGroup])) {
            foreach ($this->stringsHandler->strings[$this->router->tokenGroup] as $token => $string) {
                // detect special suffixes
                $suffix = 'textarea';
                $suffixOffset = strpos($token, '/');
                if ($suffixOffset !== false) {
                    $suffix = substr($token, $suffixOffset + 1);
                }

                // determine helper class to use for rendering token's string
                $helperClass = __NAMESPACE__ . '\TokenHelpers\\' . Utilities::formatClassName($suffix);
                $helper = new $helperClass($string, $token);

                // replace token with string in HTML
                $this->html = str_replace('[[' . $token . ']]', $helper->getString(), $this->html);
            }
        }
    }

    public function getFormFields() {
        $fields = array();
        preg_match_all('/(<input[^<]+>)|(<textarea[^<]+>.*?<\/textarea>)|(<select[^<]+>)/s', $this->html, $inputs);
        foreach ($inputs[0] as $input) {
            $pattern = '/\s+([a-z-]+)\s*(?:=\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*))?/';
            preg_match_all($pattern, $input, $attributes);
            array_walk($attributes[2], array($this, 'cleanupFormFields'));

            $keyedAttrs = array_combine($attributes[1], $attributes[2]);
            if (isset($keyedAttrs['name'])) {
                $fields[$keyedAttrs['name']] = $keyedAttrs;
            }
        }

        return $fields;
    }

    protected function cleanupFormFields(&$n) {
        $n = trim($n, ' "\'');
        if (strpos($n, 'form[') === 0) {
            $n = str_replace(array('form[', ']'), '', $n);
        }
    }

    public function replaceToken($token, $value) {
        $this->html = str_replace('[[' . $token . ']]', $value, $this->html);
    }

    public function getTokenValue($token, $replacement = '') {
        preg_match('/\[\[' . $token . '=([^\]]+)\]\]/', $this->html, $matches);

        if (count($matches)) {
            $this->html = str_replace($matches[0], $replacement, $this->html);
            return $matches[1];
        }

        return '';
    }

    public function render() {
        // replace escaped brackets
        $this->html = str_replace(array('\[', '\]'), array('[', ']'), $this->html);

        return $this->html;
    }
}