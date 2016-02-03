<?php

namespace ATC\ConfigAdapter;

class Apc implements AdapterInterface
{
    protected $key = '';
    protected $file;

    public function __construct($namespace) {
        $this->key = 'ATC::' . $namespace;
    }

    public function read() {
        if (!apc_exists($this->key)) {
            return array(
                '_global' => array(),
            );
        }
        return apc_fetch($this->key);
    }

    public function write(Array $data) {
        apc_store($this->key, $data);
    }
}