<?php
namespace ATC\TokenHelpers;

class Text
{
    protected $name = '';
    protected $label = '';
    protected $value = '';
    protected $file = 'input-text.html';
    protected $map = array();

    public function __construct($value, $name = '', $label = '') {
        $this->name = $name;
        $this->label = $label;
        $this->value = $value;

        $this->map = array(
            '[[__name]]'    => $this->name,
            '[[__label]]'   => $this->label,
            '[[__value]]'   => $this->value,
        );
    }

    public function getField() {
        return $this->render($this->file, $this->map);
    }

    public function getString() {
        return $this->value;
    }

    protected function render($file, Array $map) {
        return \ATC\TemplateHandler::render('/layouts/_system/' . $file, $map);
    }
}