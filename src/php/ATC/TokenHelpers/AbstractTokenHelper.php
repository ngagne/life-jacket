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
     * Process html array (used for repeaters)
     *
     * @param array $html
     * @return string
     */
    public function processRegion(Array $html) {
        return implode("\n", $html);
    }

    /**
     * Process instance of HTML input sub-fields (used for repeaters)
     *
     * @param array $html
     * @return string
     */
    public function processFields(Array $html) {
        return implode("\n", $html);
    }

    /**
     * Process group of HTML input sub-fields (used for repeaters)
     *
     * @param array $html
     * @return string
     */
    public function processFieldGroup(Array $html) {
        return implode("\n", $html);
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