<?php
namespace ATC\TokenHelpers;

class AbstractTokenHelper
{
    protected $name = '';
    protected $label = '';
    protected $value = '';
    protected $file = 'input-text.html';
    protected $map = array();

    /**
     * AbstractTokenHelper constructor.
     *
     * @param string $value
     * @param string $name
     * @param string $label
     */
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

    /**
     * Get the admin form input field
     *
     * @return string
     */
    public function getField() {
        return $this->render($this->file, $this->map);
    }

    /**
     * Generate HTML for display on public website
     *
     * @return string
     */
    public function getString() {
        return $this->value;
    }

    /**
     * Render admin form input field
     *
     * @param string $file
     * @param array $map
     * @return string
     */
    protected function render($file, Array $map) {
        return \ATC\TemplateHandler::render('/layouts/_system/' . $file, $map);
    }
}