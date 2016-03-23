<?php

namespace ATC;


class StringsHandler
{
    public $strings;
    protected $adapter;

    public function __construct() {
        $config = Config::getInstance();
        $adapterClass = __NAMESPACE__ . '\ConfigAdapter\\' . Utilities::formatClassName($config->get('strings_adapter', 'ini'));
        $this->adapter = new $adapterClass('strings');

        $this->loadStrings();
    }

    public function rebuildStrings() {
        $foundStrings = array(
            '_global' => array(),
        );

        // get layout files
        $path = APPLICATION_PATH . '/layouts/';
        $files = array_map(array($this, 'mapLayoutPaths'), array_diff(scandir($path), array('..', '.', '_partials')));

        // get layout partials
        $path = APPLICATION_PATH . '/layouts/_partials/';
        $files = array_merge($files, array_map(array($this, 'mapPartialsPaths'), array_diff(scandir($path), array('..', '.'))));

        // process each file
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }

            // get file contents
            $html = file_get_contents($file);

            // parse globals
            preg_match_all('@\[\[(_[0-9a-zA-Z][/0-9a-zA-Z_-]*)\]\]@', $html, $matches);
            if (!empty($matches[1])) {
                $foundStrings['_global'] = array_merge($foundStrings['_global'], array_fill_keys($matches[1], ''));
            }
        }

        // get template files
        $foundStrings = $this->getTemplates($foundStrings);

        // check if data has changed
        $isNewData = $this->isStringsChanged($foundStrings, $this->strings);

        if ($isNewData) {
            // update strings list
            $newStrings = array();
            foreach ($foundStrings as $pageID => $tokens) {
                $strings = array();

                foreach ($tokens as $token => $value) {
                    $strings[$token] = isset($this->strings[$pageID]) && !empty($this->strings[$pageID][$token]) ? $this->strings[$pageID][$token] : '';
                }

                $newStrings[$pageID] = $strings;
            }

            $this->strings = $newStrings;

            // save strings
            $this->saveStrings($this->strings);
        }
    }

    protected function isStringsChanged($new, $old) {
        foreach ($new as $id => $tokens) {
            if (!isset($old[$id]) || array_diff(array_keys($new[$id]), array_keys($old[$id]))) {
                return true;
            }
        }
        return false;
    }

    protected function mapLayoutPaths($file) {
        return APPLICATION_PATH . '/layouts/' . $file;
    }

    protected function mapPartialsPaths($file) {
        return APPLICATION_PATH . '/layouts/_partials/' . $file;
    }

    protected function getTemplates($foundStrings, $dir = '') {
        $path = APPLICATION_PATH . '/views/' . $dir;
        $files = array_diff(scandir($path), array('..', '.'));

        // process each files
        foreach ($files as $file) {
            if (is_dir($path . $file)) {
                $foundStrings = $this->getTemplates($foundStrings, $dir . $file . '/');
                continue;
            }

            // get file contents
            $html = file_get_contents($path . $file);

            // parse global
            preg_match_all('@\[\[(_[0-9a-zA-Z][/0-9a-zA-Z_-]*)\]\]@', $html, $matches);
            if (!empty($matches[1])) {
                $foundStrings['_global'] = array_merge($foundStrings['_global'], array_fill_keys($matches[1], ''));
            }

            // parse page specific
            $pageID = $dir . str_replace('.html', '', $file);
            preg_match_all('@\[\[([0-9a-zA-Z][/0-9a-zA-Z_-]*)\]\]@', $html, $matches);
            if (!empty($matches[1])) {
                if (!isset($foundStrings[$pageID])) {
                    $foundStrings[$pageID] = array();
                }
                $foundStrings[$pageID] = array_merge($foundStrings[$pageID], array_fill_keys($matches[1], ''));
            }
        }

        return $foundStrings;
    }

    public function saveStrings() {
        return $this->adapter->write($this->strings);
    }
    public function setStrings($strings) {
        $this->strings = $strings;
        $this->saveStrings();
    }

    protected function loadStrings() {
        $this->strings = $this->adapter->read();
    }
}