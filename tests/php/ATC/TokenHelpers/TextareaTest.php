<?php

class TokenHelpersTextareaTest extends TokenHelpersAbstractHelperTest
{
    protected $file = 'input-textarea.html';
    protected $class = '\ATC\TokenHelpers\Textarea';

    /**
     * @depends testCanGetField
     */
    public function testCanGetString($helper) {
        $this->assertEquals("<p>About Us</p><br />\n<em>Test</em>", $helper->getString());

        return $helper;
    }

}