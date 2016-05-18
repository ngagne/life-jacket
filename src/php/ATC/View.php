<?php

namespace ATC;

class View
{
    protected $html;
    protected $router;
    protected $stringsHandler;

    /**
     * View constructor.
     *
     * @param Router $router
     * @param StringsHandler $stringsHandler
     */
    public function __construct(\ATC\Router $router, \ATC\StringsHandler $stringsHandler) {
        $this->router = $router;
        $this->stringsHandler = $stringsHandler;

        $this->process();
    }

    /**
     * Load template files and process tokens
     *
     * @throws \Exception
     */
    protected function process() {
        $this->loadTemplateFiles();
        $this->html = $this->processTokens($this->router->tokenGroup, $this->stringsHandler->strings, $this->html);
    }

    /**
     * Load template files based on parsed route
     *
     * @throws \Exception
     */
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
        $this->html = $this->processIncludes($this->html);
    }

    /**
     * Recursively find and replace include tokens with the partial HTML content
     *
     * @param $html
     * @return mixed
     * @throws \Exception
     */
    protected function processIncludes($html, $path = '') {
        preg_match_all('/\[\[include=([0-9a-zA-Z_-]+)\]\]/', $html, $matches);
        if (count($matches)) {
            foreach ($matches[1] as $i => $match) {
                // prevent infinite loop of including the same file
                if ($match == $path) {
                    // simply remove token, breaking the loop
                    $html = str_replace($matches[0][$i], '', $html);
                    continue;
                }

                $file = APPLICATION_PATH . '/layouts/_partials/' . $match . '.html';
                if (file_exists($file)) {
                    $html = str_replace($matches[0][$i], $this->processIncludes(file_get_contents($file), $match), $html);
                } else {
                    throw new \Exception('Include was not found: ' . $file);
                }
            }
        }

        return $html;
    }

    /**
     * Process tokens found within templates
     *
     * @param string $tokenGroup
     * @param array $strings
     * @param string $html
     * @return string
     */
    protected function processTokens($tokenGroup, $strings, $html) {
        // process any global tokens
        if (!empty($strings['_global'])) {
            foreach ($strings['_global'] as $token => $value) {
                $html = str_replace('[[' . $token . ']]', $value, $html);
            }
        }

        // process any action specific tokens
        if (isset($strings[$tokenGroup])) {
            foreach ($strings[$tokenGroup] as $token => $value) {
                // detect special suffixes
                $suffix = 'textarea';
                $suffixOffset = strpos($token, '/');
                if ($suffixOffset !== false) {
                    $suffix = substr($token, $suffixOffset + 1);
                }

                // determine helper class to use for rendering token's string
                $helperClass = __NAMESPACE__ . '\TokenHelpers\\' . Utilities::formatClassName($suffix);
                $helper = new $helperClass($value, $token);

                // replace token with string in HTML
                if (!is_array($value)) {
                    $html = str_replace('[[' . $token . ']]', $helper->getString(), $html);
                } else {
                    // get html slice within repeater region
                    if (!preg_match('@\[\[' . $token . '\]\](.*?)\[\[/' . $token . '\]\]@mis', $html, $match)) {
                        continue;
                    }
                    $htmlSlice = $match[1];

                    // iterate through and process tokens each item
                    $output = array();
                    foreach ($value as $subStrings) {
                        $output[] = $this->processTokens(0, array($subStrings), $htmlSlice);
                    }

                    // process item results with repeater token helper
                    $htmlSlice = $helper->processRegion($output);

                    // replace original html region slice with new content
                    $html = preg_replace('@\[\[' . $token . '\]\].*?\[\[/' . $token . '\]\]@mis', $htmlSlice, $html);
                }
            }
        }

        return $html;
    }

    /**
     * Parse form fields from templates
     *
     * @return array
     */
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

    /**
     * Reformat name of form fields
     *
     * @param string $n
     */
    protected function cleanupFormFields(&$n) {
        $n = trim($n, ' "\'');
        if (strpos($n, 'form[') === 0) {
            $n = str_replace(array('form[', ']'), '', $n);
        }
    }

    /**
     * Replace token with value in HTML
     *
     * @param string $token
     * @param string $value
     */
    public function replaceToken($token, $value) {
        $this->html = str_replace('[[' . $token . ']]', $value, $this->html);
    }

    /**
     * Get the value of a token and replace that token in the HTML
     *
     * @param string $token
     * @param string $replacement
     * @return string
     */
    public function getTokenValue($token, $replacement = '') {
        preg_match('/\[\[' . $token . '=([^\]]+)\]\]/', $this->html, $matches);

        if (count($matches)) {
            $this->html = str_replace($matches[0], $replacement, $this->html);
            return $matches[1];
        }

        return '';
    }

    /**
     * Get final rendered HTML, ready for output to the browser
     *
     * @return mixed
     */
    public function render() {
        // replace escaped brackets
        $this->html = str_replace(array('\[', '\]'), array('[', ']'), $this->html);

        return $this->html;
    }
}