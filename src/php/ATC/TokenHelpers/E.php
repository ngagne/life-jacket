<?php
namespace ATC\TokenHelpers;

class E extends AbstractTokenHelper
{

    public function getString() {
        return strip_tags($this->value);
    }
}