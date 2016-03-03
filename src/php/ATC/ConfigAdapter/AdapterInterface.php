<?php

namespace ATC\ConfigAdapter;

interface AdapterInterface
{
    /**
     * AdapterInterface constructor.
     *
     * @param string $namespace
     */
    public function __construct($namespace);

    /**
     * Get config object from storage
     *
     * @return mixed
     */
    public function read();

    /**
     * Update config object in storage
     *
     * @param array $data
     * @return mixed
     */
    public function write(Array $data);
}