<?php

namespace ATC;


class StringsHandler
{
    public $strings;
    protected $adapter;

    /**
     * StringsHandler constructor.
     */
    public function __construct() {
        $config = Config::getInstance();
        $adapterClass = __NAMESPACE__ . '\ConfigAdapter\\' . Utilities::formatClassName($config->get('strings_adapter', 'ini'));
        $this->adapter = new $adapterClass('strings');

        $this->loadStrings();
    }

    /**
     * Rebuild list of tokens found in all template/layout files
     */
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
                    if (is_array($value)) {
                        if (!isset($this->strings[$pageID][$token]) || !is_array($this->strings[$pageID][$token])) {
                            $strings[$token] = $value;
                        } else {
                            $strings[$token] = array_merge($value, $this->strings[$pageID][$token]);
                        }

                        // set default empty arrays
                        $maxArraySize = 1;
                        foreach ($strings[$token] as $i => $string) {
                            if (!is_array($string)) {
                                $strings[$token][$i] = array('');
                            } else {
                                $maxArraySize = max($maxArraySize, count($strings[$token][$i]));
                            }
                        }

                        // pad arrays if needed
                        foreach ($strings[$token] as $i => $string) {
                            if (count($string) < $maxArraySize) {
                                $strings[$token][$i] = array_pad($strings[$token][$i], $maxArraySize, '');
                            }
                        }
                    } else {
                        $strings[$token] = isset($this->strings[$pageID]) && !empty($this->strings[$pageID][$token]) ? $this->strings[$pageID][$token] : '';
                    }
                }

                $newStrings[$pageID] = $strings;
            }

            $this->strings = $newStrings;

            // save strings
            $this->saveStrings();
        }
    }

    /**
     * Determine if a particular token has been modified
     *
     * @param string $new
     * @param string $old
     * @return bool
     */
    protected function isStringsChanged($new, $old) {
        foreach ($new as $id => $tokens) {
            foreach ($tokens as $token => $value) {
                if (is_array($value) && (!isset($old[$id][$token]) || !is_array($old[$id][$token]) || array_keys($value) != array_keys($old[$id][$token]))) {
                    return true;
                }
            }

            if (!isset($old[$id]) || array_diff(array_keys($new[$id]), array_keys($old[$id]))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get mapped path of a layout file
     *
     * @param string $file
     * @return string
     */
    protected function mapLayoutPaths($file) {
        return APPLICATION_PATH . '/layouts/' . $file;
    }

    /**
     * Get mapped path of a partials file
     *
     * @param string $file
     * @return string
     */
    protected function mapPartialsPaths($file) {
        return APPLICATION_PATH . '/layouts/_partials/' . $file;
    }

    /**
     * Get tokens by recursively searching in template files
     *
     * @param $foundStrings
     * @param string $dir
     * @return mixed
     */
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
            preg_match_all('@\[\[(/?[0-9a-zA-Z][/0-9a-zA-Z_-]*)\]\]@', $html, $matches);

            if (!empty($matches[1])) {
                if (!isset($foundStrings[$pageID])) {
                    $foundStrings[$pageID] = array();
                }
                $foundStrings[$pageID] = array_merge($foundStrings[$pageID], array_fill_keys($matches[1], ''));

                // handle repeaters
                $keys = array_keys($foundStrings[$pageID]);
                foreach ($keys as $key) {
                    // is token a closing repeater?
                    if (strpos($key, '/') === 0) {
                        $children = array();
                        $target = ltrim($key, '/');
                        $reversedKeys = array_reverse($keys);

                        // remove from list
                        unset($foundStrings[$pageID][$key]);

                        // search for matching opening token
                        $isPastClosing = false;
                        foreach ($reversedKeys as $reversedKey) {
                            // skip tokens that aren't within the region of the repeater
                            if (!$isPastClosing) {
                                if ($reversedKey == $key) {
                                    $isPastClosing = true;
                                }
                                continue;
                            }

                            // check if this is the opening token
                            if ($target == $reversedKey) {
                                $foundStrings[$pageID][$reversedKey] = array_fill_keys($children, '');
                                break;
                            }

                            // this token is within the repeater region, add it to the list of children
                            $children[] = $reversedKey;
                            unset($foundStrings[$pageID][$reversedKey]);
                        }
                    }
                }
            }
        }

        return $foundStrings;
    }

    /**
     * Save all tokens to storage
     *
     * @return mixed
     */
    public function saveStrings() {
        return $this->adapter->write($this->strings);
    }

    /**
     * Update value of tokens
     *
     * @param array $strings
     */
    public function setStrings($strings) {
        $this->strings = $strings;
        $this->saveStrings();
    }

    /**
     * Get tokens from storage
     */
    protected function loadStrings() {
        $this->strings = $this->adapter->read();
    }
}