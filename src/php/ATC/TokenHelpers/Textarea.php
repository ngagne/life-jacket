<?php
namespace ATC\TokenHelpers;

class Textarea extends AbstractTokenHelper
{
    protected $file = 'input-textarea.html';

    public function getString() {
        return nl2br($this->value);
    }
}