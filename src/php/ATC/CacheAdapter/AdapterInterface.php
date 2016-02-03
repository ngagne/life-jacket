<?php

namespace ATC\CacheAdapter;

interface AdapterInterface
{
    public function store($key, $value);
    public function fetch($key, $default = '');
    public function delete($key);
    public function clear();
}