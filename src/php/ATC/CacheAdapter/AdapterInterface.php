<?php

namespace ATC\CacheAdapter;

interface AdapterInterface
{
    /**
     * Store a value in cache
     *
     * @param string $key
     * @param string $value
     */
    public function store($key, $value);

    /**
     * Fetch a value from cache
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    public function fetch($key, $default = '');

    /**
     * Delete a value from cache
     *
     * @param string $key
     */
    public function delete($key);

    /**
     * Clear all values from cache
     */
    public function clear();
}