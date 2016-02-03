<?php

namespace ATC\ConfigAdapter;

class Ini implements AdapterInterface
{
    protected $file;

    public function __construct($namespace) {
        $this->file = APPLICATION_PATH . '/config/' . $namespace . '.ini';
    }

    public function read() {
        if (!file_exists($this->file)) {
            throw new \Exception('INI file was not found: ' . $this->file);
        }

        return parse_ini_file($this->file, true);
    }

    public function write(Array $data) {
        // build file contents
        $lines = array();
        foreach($data as $key => $val) {
            if (is_array($val)) {
                $lines[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    $lines[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.$sval.'"');
                }
                $lines[] = "";
            } else {
                $lines[] = "$key = ".(is_numeric($val) ? $val : '"'.$val.'"');
            }
        }

        // save data to file
        file_put_contents($this->file, implode("\n", $lines));
    }
}