<?php

class ConfigAdapterIniTest extends PHPUnit_Framework_TestCase
{
    protected static $file;
    protected static $ini;

    public static function setUpBeforeClass() {
        // init
        $uniq = time();
        $configDir = APPLICATION_PATH . '/config/';

        $ini = 'atc-unit-test-' . $uniq;
        $file = $configDir . $ini . '.ini';
        file_put_contents($file, implode("\n", array(
            '[production]',
            'site_name = "Test 1"',
            'site_root = "/"',
            'image_uploads_path = "/img/uploads/"',
            '[development]',
            'site_name = "Test 2"',
            'image_uploads_path = "/test/img/uploads/"',
        )));

        self::$file = $file;
        self::$ini = $ini;
    }

    public function testCanRead() {
        $adapter = new \ATC\ConfigAdapter\Ini(self::$ini);
        $config = $adapter->read();

        $this->assertNotNull($config);
        $this->assertEquals(2, count($config));
        $this->assertNotNull($config['production']);
        $this->assertNotEmpty($config['production']);
        $this->assertNotNull($config['development']);
        $this->assertNotEmpty($config['development']);
        $this->assertEquals(3, count($config['production']));
        $this->assertEquals(2, count($config['development']));
        $this->assertEquals('Test 1', $config['production']['site_name']);
        $this->assertEquals('Test 2', $config['development']['site_name']);
        $this->assertEquals('/img/uploads/', $config['production']['image_uploads_path']);
        $this->assertEquals('/test/img/uploads/', $config['development']['image_uploads_path']);
        $this->assertNull($config['development']['site_root']);
    }

    public function testCanWrite() {
        $adapter = new \ATC\ConfigAdapter\Ini(self::$ini);
        $config = $adapter->read();

        // create values to test with
        $config['development']['site_root'] = '/test/';
        $config['development']['site_name'] = 'Life Jacket';
        $config['staging'] = array(
            'site_name' => 'Test 3',
        );

        // store values
        $adapter->write($config);

        // check that values were written properly
        $this->assertNotNull($config);
        $this->assertEquals(3, count($config));
        $this->assertNotNull($config['production']);
        $this->assertNotEmpty($config['production']);
        $this->assertNotNull($config['development']);
        $this->assertNotEmpty($config['development']);
        $this->assertNotNull($config['staging']);
        $this->assertNotEmpty($config['staging']);
        $this->assertEquals(3, count($config['production']));
        $this->assertEquals(3, count($config['development']));
        $this->assertEquals(1, count($config['staging']));
        $this->assertEquals('Test 1', $config['production']['site_name']);
        $this->assertEquals('Life Jacket', $config['development']['site_name']);
        $this->assertEquals('Test 3', $config['staging']['site_name']);
        $this->assertEquals('/img/uploads/', $config['production']['image_uploads_path']);
        $this->assertEquals('/test/img/uploads/', $config['development']['image_uploads_path']);
        $this->assertEquals('/test/', $config['development']['site_root']);
    }

    public static function tearDownAfterClass() {
        // cleanup
        unlink(self::$file);
    }
}