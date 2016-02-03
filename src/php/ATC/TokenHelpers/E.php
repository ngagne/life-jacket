<?php
namespace ATC\TokenHelpers;

class E extends Text
{

    public function getString() {
        return strip_tags($this->value);
    }
}