<?php

namespace ATC\ConfigAdapter;

class Memcached implements AdapterInterface
{
    protected $key = '';
    protected $file;

    public function __construct($namespace) {
        $this->key = 'ATC::' . $namespace;

        // check for availability
        if (!class_exists('Memcached')) {
            throw new \Exception("The Memcached extension for PHP doesn't appear to be installed.");
        }

        $this->memcached = new \Memcached();
    }

    public function read() {
        return $this->memcached->fetch($this->key, array(
            '_global' => array(),
        ));
    }

    public function write(Array $data) {
        $this->memcached->store($this->key, $data);
    }
}