<?php

namespace ATC;

/**
 * Class Cache
 * @package ATC
 */
class Cache
{
    private static $instance;
    protected $adapter;

    protected function __construct() {}
    private function __clone() {}

    /**
     * Get singleton instance
     *
     * @return Cache
     */
    public static function getInstance() {
        if (null === static::$instance) {
            $config = Config::getInstance();
            $adapterClass = __NAMESPACE__ . '\CacheAdapter\\' . Utilities::formatClassName($config->get('cache_adapter', 'file'));

            static::$instance = new static();
            static::$instance->adapter = new $adapterClass();
        }

        return static::$instance;
    }

    /**
     * Store a single cache entry
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function store($key, $value) {
        return static::$instance->adapter->store($key, $value);
    }

    /**
     * Fetch a single cache entry
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function fetch($key, $default = '') {
        return static::$instance->adapter->fetch($key, $default);
    }

    /**
     * Delete a single cache entry
     *
     * @param string $key
     * @return mixed
     */
    public function delete($key) {
        return static::$instance->adapter->delete($key);
    }

    /**
     * Delete all cache entries
     *
     * @return mixed
     */
    public function clear() {
        return static::$instance->adapter->clear();
    }
}