<?php

class UtilitiesTest extends PHPUnit_Framework_TestCase
{
    public function testCanFormatClassName() {
        $this->assertEquals('About', \ATC\Utilities::formatClassName('about'));
        $this->assertEquals('About2', \ATC\Utilities::formatClassName('about2'));
        $this->assertEquals('About2', \ATC\Utilities::formatClassName('about-2'));
        $this->assertEquals('AboutUs', \ATC\Utilities::formatClassName('about-us'));
        $this->assertEquals('About_us', \ATC\Utilities::formatClassName('about_us'));
        $this->assertEquals('AboutOurCompany', \ATC\Utilities::formatClassName('about-our-company'));
        $this->assertEquals('About_our_company', \ATC\Utilities::formatClassName('about_our_company'));
        $this->assertEquals('Test', \ATC\Utilities::formatClassName('TEST'));
        $this->assertEquals('AboutUs', \ATC\Utilities::formatClassName('ABOUT-US'));
        $this->assertEquals('About_us', \ATC\Utilities::formatClassName('ABOUT_US'));
    }

    public function testCanFormatActionName() {
        $this->assertEquals('about', \ATC\Utilities::formatActionName('about'));
        $this->assertEquals('about2', \ATC\Utilities::formatActionName('about2'));
        $this->assertEquals('about2', \ATC\Utilities::formatActionName('about-2'));
        $this->assertEquals('aboutUs', \ATC\Utilities::formatActionName('about-us'));
        $this->assertEquals('about_us', \ATC\Utilities::formatActionName('about_us'));
        $this->assertEquals('aboutOurCompany', \ATC\Utilities::formatActionName('about-our-company'));
        $this->assertEquals('about_our_company', \ATC\Utilities::formatActionName('about_our_company'));
        $this->assertEquals('test', \ATC\Utilities::formatActionName('TEST'));
        $this->assertEquals('aboutUs', \ATC\Utilities::formatActionName('ABOUT-US'));
        $this->assertEquals('about_us', \ATC\Utilities::formatActionName('ABOUT_US'));
    }
}