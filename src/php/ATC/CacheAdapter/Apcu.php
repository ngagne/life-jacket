<?php

namespace ATC\CacheAdapter;

use ATC\Config;

class Apcu implements AdapterInterface
{
    protected $prefix = '';

    public function __construct() {
        $config = Config::getInstance();
        $this->prefix = $config->get('cache_prefix', 'ATC::');

        // check for availability
        if (!function_exists('apcu_store')) {
            throw new \Exception("The APCu extension for PHP doesn't appear to be installed.");
        }
    }

    public function store($key, $value) {
        return apcu_store($this->hash($key), $value);
    }
    public function fetch($key, $default = '') {
        if (!apcu_exists($this->hash($key))) {
            return $default;
        }
        return apcu_fetch($this->hash($key));
    }
    public function delete($key) {
        apcu_delete($this->hash($key));
    }
    public function clear() {
        $iterator = new \APCUIterator('user', '/^ATC::/', APC_ITER_VALUE);
        apcu_delete($iterator);
    }
    protected function hash($key) {
        return $this->prefix . $key;
    }
}