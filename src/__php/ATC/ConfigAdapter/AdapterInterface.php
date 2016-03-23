<?php

namespace ATC\ConfigAdapter;

interface AdapterInterface
{
    public function __construct($namespace);
    public function read();
    public function write(Array $data);
}