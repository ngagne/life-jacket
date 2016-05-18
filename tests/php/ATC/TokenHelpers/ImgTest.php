<?php
use ATC\Config;

class TokenHelpersImgTest extends TokenHelpersAbstractHelperTest
{
    protected $file = 'input-file.html';
    protected $class = '\ATC\TokenHelpers\Img';
    protected $map = array(
        '[[__name]]'    => 'img',
        '[[__label]]'   => 'Image',
        '[[__value]]'   => "sample/image/path.png",
        '[[__img]]'     => '',
    );
    protected $imgHTML = '';

    public function setUp() {
        $config = Config::getInstance();
        $this->imgHTML = '/' . trim($config->image_uploads_path, '/') . '/sample~~image~~path.png';
        $this->map['[[__img]]'] = str_replace('[[__value]]', $this->imgHTML, file_get_contents(APPLICATION_PATH . '/layouts/_system/input-img.html'));

        $this->targetHTML = str_replace(array_keys($this->map), $this->map, file_get_contents(APPLICATION_PATH . '/layouts/_system/' . $this->file));
    }

    /**
     * @depends testCanGetField
     */
    public function testCanGetString($helper) {
        $this->assertEquals($this->imgHTML, $helper->getString());

        return $helper;
    }

    public function testCanGetField() {
        return parent::testCanGetField();
    }
}