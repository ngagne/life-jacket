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

        $data = parse_ini_file($this->file, true);

        // handle nested elements
        foreach ($data as $pageID => $items) {
            foreach ($items as $key => $item) {
                if (strpos($key, '//') !== false) {
                    list($parentKey, $subKey) = explode('//', $key);

                    if (!isset($data[$pageID][$parentKey])) {
                        $data[$pageID][$parentKey] = array();
                    }

                    $data[$pageID][$parentKey][$subKey] = $item;

                    unset($data[$pageID][$key]);
                }
            }
        }

        // rearrange nested elements
        foreach ($data as $pageID => $items) {
            foreach ($items as $key => $item) {
                if (is_array($item)) {
                    $organizedData = array();
                    foreach ($item as $subKey => $subItems) {
                        foreach ($subItems as $i => $subItem) {
                            $organizedData[$subKey][] = $subItem;
                        }
                    }
                    $data[$pageID][$key] = $organizedData;
                }
            }
        }

        return $data;
    }

    public function write(Array $data) {
        // build file contents
        $lines = array();
        foreach($data as $key => $val) {
            if (is_array($val)) {
                $lines[] = "[$key]";
                foreach ($val as $skey => $sval) {
                    $lines = $this->writeLine($lines, $skey, $sval);
                }
                $lines[] = "";
            } else {
                $lines = $this->writeLine($lines, $key, $val);
            }
        }

        // save data to file
        file_put_contents($this->file, implode("\n", $lines));
    }

    protected function writeLine($lines, $key, $value) {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValues) {
                foreach ($subValues as $subValue) {
                    $lines[] = $key . '//' . $subKey . '[] = ' . '"' . addslashes($subValue) . '"';
                }
            }
        } else {
            if (!is_numeric($value)) {
                $value = '"' . addslashes($value) . '"';
            }
            $lines[] = "$key = " . $value;
        }

        return $lines;
    }
}