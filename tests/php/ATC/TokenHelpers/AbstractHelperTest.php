<?php

class TokenHelpersAbstractHelperTest extends PHPUnit_Framework_TestCase
{
    protected $file = 'input-text.html';
    protected $map = array(
        '[[__name]]'    => 'headline',
        '[[__label]]'   => 'Headline',
        '[[__value]]'   => "<p>About Us</p>\n<em>Test</em>",
    );
    protected $targetHTML;
    protected $class = '\ATC\TokenHelpers\AbstractTokenHelper';

    public function setUp() {
        $this->targetHTML = str_replace(array_keys($this->map), $this->map, file_get_contents(APPLICATION_PATH . '/layouts/_system/' . $this->file));
    }

    public function testCanGetField() {
        $helper = new $this->class($this->map['[[__value]]'], $this->map['[[__name]]'], $this->map['[[__label]]']);

        $this->assertEquals($this->targetHTML, $helper->getField());

        return $helper;
    }

    /**
     * @depends testCanGetField
     */
    public function testCanGetString($helper) {
        $this->assertEquals($this->map['[[__value]]'], $helper->getString());

        return $helper;
    }

    /**
     * @depends testCanGetString
     */
    public function testCanRender($helper) {
        $this->assertEquals($this->targetHTML, $helper->getField());
    }
}