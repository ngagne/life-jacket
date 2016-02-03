<?php

namespace ATC\ConfigAdapter;

class Apcu implements AdapterInterface
{
    protected $key = '';
    protected $file;

    public function __construct($namespace) {
        $this->key = 'ATC::' . $namespace;
    }

    public function read() {
        if (!apcu_exists($this->key)) {
            return array(
                '_global' => array(),
            );
        }
        return apcu_fetch($this->key);
    }

    public function write(Array $data) {
        apcu_store($this->key, $data);
    }
}