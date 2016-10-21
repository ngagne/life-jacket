<?php
namespace ATC\TokenHelpers;

class Repeater extends AbstractTokenHelper
{
    public function processFields(Array $html) {
        return '<li class="row field-group">' . implode("\n", $html) . '</li>';
    }

    public function processFieldGroup(Array $html) {
        $map = array(
            '[[__label]]' => $this->label,
            '[[__value]]' => implode("\n", $html),
        );
        return \ATC\TemplateHandler::render('/layouts/_system/input-repeater.html', $map);
    }
}