<?php

namespace ATC\CacheAdapter;

use ATC\Config;

class Memcached implements AdapterInterface
{
    protected $base = '';
    protected $prefix = '';
    protected $memcached;

    public function __construct() {
        $config = Config::getInstance();
        $this->base = $config->get('cache_prefix', 'ATC::');

        // check for availability
        if (!class_exists('Memcached')) {
            throw new \Exception("The Memcached extension for PHP doesn't appear to be installed.");
        }

        $this->memcached = new \Memcached();

        // get stored key prefix
        $prefix = $this->memcached->fetch($this->base . 'prefix');
        if ($prefix === false) {
            $prefix = time();
            $this->memcached->store($this->base . 'prefix', $prefix);
        }
        $this->prefix = $this->base . $prefix . '::';
    }

    public function store($key, $value) {
        return $this->memcached->store($this->hash($key), $value);
    }
    public function fetch($key, $default = '') {
        return $this->memcached->fetch($this->hash($key), $default);
    }
    public function delete($key) {
        $this->memcached->delete($this->hash($key));
    }
    public function clear() {
        $prefix = time();
        $this->memcached->store($this->base . 'prefix', $prefix);
        $this->prefix = $this->base . $prefix . '::';
    }
    protected function hash($key) {
        return $this->prefix . $key;
    }
}