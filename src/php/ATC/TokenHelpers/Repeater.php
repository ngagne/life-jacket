<?php
namespace ATC\TokenHelpers;

use ATC\Config;

class Repeater extends AbstractTokenHelper
{
    public function processFields(Array $html) {
        return '<div class="well">' . implode("\n", $html) . '</div>';
    }

    public function processFieldGroup(Array $html) {
        return '<div class="row field-group">' . implode("\n", $html) . '</div>';
    }
}