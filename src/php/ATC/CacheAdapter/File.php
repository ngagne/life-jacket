<?php

namespace ATC\CacheAdapter;

use ATC\Config;

class File implements AdapterInterface
{
    protected $path = '';

    public function __construct() {
        $config = Config::getInstance();
        $this->path = rtrim($config->get('cache_file_path', APPLICATION_PATH . '/../tmp/'), '/') . '/';

        // check for file permissions
        if (!is_dir($this->path)) {
            throw new \Exception("Cache directory was not found: " . $this->path);
        } else if (!is_writable($this->path)) {
            throw new \Exception('Cache directory is not writable: ' . $this->path);
        }
    }

    public function store($key, $value) {
        return file_put_contents($this->path . $this->hash($key), $value);
    }
    public function fetch($key, $default = '') {
        $file = $this->path . $this->hash($key);
        if (!file_exists($file)) {
            return $default;
        }
        return file_get_contents($file);
    }
    public function delete($key) {
        unlink($this->path . $this->hash($key));
    }
    public function clear() {
        array_map('unlink', glob($this->path . '*.cache'));
    }
    protected function hash($key) {
        return hash('md5', $key) . '.cache';
    }
}