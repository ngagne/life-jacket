<?php

class TokenHelpersETest extends TokenHelpersAbstractHelperTest
{
    /**
     * @depends testCanGetField
     */
    public function testCanGetString($helper) {
        $this->assertEquals(strip_tags(self::$map['[[__value]]']), $helper->getString());

        return $helper;
    }
}