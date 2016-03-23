<?php

namespace ATC\CacheAdapter;

use ATC\Config;

class Apc implements AdapterInterface
{
    protected $prefix = '';

    public function __construct() {
        $config = Config::getInstance();
        $this->prefix = $config->get('cache_prefix', 'ATC::');

        // check for availability
        if (!function_exists('apc_store')) {
            throw new \Exception("The APC extension for PHP doesn't appear to be installed.");
        }
    }

    public function store($key, $value) {
        return apc_store($this->hash($key), $value);
    }
    public function fetch($key, $default = '') {
        if (!apc_exists($this->hash($key))) {
            return $default;
        }
        return apc_fetch($this->hash($key));
    }
    public function delete($key) {
        apc_delete($this->hash($key));
    }
    public function clear() {
        $iterator = new \APCIterator('user', '/^ATC::/', APC_ITER_VALUE);
        apc_delete($iterator);
    }
    protected function hash($key) {
        return $this->prefix . $key;
    }
}