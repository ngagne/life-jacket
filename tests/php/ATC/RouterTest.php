<?php

class RouterTest extends PHPUnit_Framework_TestCase
{
    protected static $uniq;
    protected static $templateDir;
    protected static $reqFiles;
    protected static $reqDirs;

    public static function setUpBeforeClass() {
        // init
        $uniq = time();
        $templateDir = APPLICATION_PATH . '/views/';
        $reqFiles = array(
            'atc-unit-test1-' . $uniq . '.html',
            'atc-unit-test2-' . $uniq . '.html',
            'atc-unit-test2-' . $uniq . '/index.html',
            'atc-unit-test2-' . $uniq . '/test.html',
            'atc-unit-test2-' . $uniq . '/sub/test.html',
            'atc-unit-test2-' . $uniq . '/sub/test2.html',
            'atc-unit-test3-' . $uniq . '.html',
        );
        $reqDirs = array(
            'atc-unit-test2-' . $uniq,
            'atc-unit-test2-' . $uniq . '/sub',
            'atc-unit-test3-' . $uniq,
        );
        foreach ($reqDirs as $reqDir) {
            mkdir($templateDir . $reqDir);
        }
        foreach ($reqFiles as $reqFile) {
            touch($templateDir . $reqFile);
        }

        self::$templateDir = $templateDir;
        self::$uniq = $uniq;
        self::$reqFiles = $reqFiles;
        self::$reqDirs = $reqDirs;
    }

    public function testCanParseRequest() {
        $a = new \ATC\Router('/');
        $b = new \ATC\Router('/index');
        $c = new \ATC\Router('/index/');
        $d = new \ATC\Router('/about-us');
        $e = new \ATC\Router('/about-us/staff');
        $f = new \ATC\Router('/about-us/staff/ceo');
        $g = new \ATC\Router('/about-us/staff/ceo/');

        $this->assertEquals('index', $a->action);
        $this->assertEquals('index', $b->action);
        $this->assertEquals('index', $c->action);
        $this->assertEquals('about-us', $d->action);
        $this->assertEquals('staff', $e->action);
        $this->assertEquals('ceo', $f->action);
        $this->assertEquals('ceo', $g->action);
    }

    public function testCanFindTemplate() {
        $uniq = self::$uniq;
        $templateDir = self::$templateDir;

        $a = new \ATC\Router('/');
        $b = new \ATC\Router('/index');
        $c = new \ATC\Router('/index/');
        $d = new \ATC\Router('/atc-unit-test1-' . $uniq);
        $e = new \ATC\Router('/atc-unit-test2-' . $uniq);
        $f = new \ATC\Router('/atc-unit-test2-' . $uniq . '/index');
        $g = new \ATC\Router('/atc-unit-test2-' . $uniq . '/test');
        $h = new \ATC\Router('/atc-unit-test2-' . $uniq . '/sub');
        $i = new \ATC\Router('/atc-unit-test2-' . $uniq . '/sub/test');
        $j = new \ATC\Router('/atc-unit-test2-' . $uniq . '/test2');
        $k = new \ATC\Router('/atc-unit-test3-' . $uniq);
        $l = new \ATC\Router('/admin');
        $m = new \ATC\Router('/admin-login');

        $this->assertEquals($templateDir . 'index.html', $a->template);
        $this->assertEquals($templateDir . 'index.html', $b->template);
        $this->assertEquals($templateDir . 'index.html', $c->template);
        $this->assertEquals($templateDir . 'atc-unit-test1-' . $uniq . '.html', $d->template);
        $this->assertEquals($templateDir . 'atc-unit-test2-' . $uniq . '/index.html', $e->template);
        $this->assertEquals($templateDir . 'atc-unit-test2-' . $uniq . '/index.html', $f->template);
        $this->assertEquals($templateDir . 'atc-unit-test2-' . $uniq . '/test.html', $g->template);
        $this->assertEquals('', $h->template);
        $this->assertEquals($templateDir . 'atc-unit-test2-' . $uniq . '/sub/test.html', $i->template);
        $this->assertEquals('', $j->template);
        $this->assertEquals($templateDir . 'atc-unit-test3-' . $uniq . '.html', $k->template);
        $this->assertEquals($templateDir . 'admin.html', $l->template);
        $this->assertEquals($templateDir . 'admin-login.html', $m->template);
    }

    public function testCanFindTokenGroups() {
        $uniq = self::$uniq;

        $a = new \ATC\Router('/');
        $b = new \ATC\Router('/index');
        $c = new \ATC\Router('/index/');
        $d = new \ATC\Router('/atc-unit-test1-' . $uniq);
        $e = new \ATC\Router('/atc-unit-test2-' . $uniq);
        $f = new \ATC\Router('/atc-unit-test2-' . $uniq . '/index');
        $g = new \ATC\Router('/atc-unit-test2-' . $uniq . '/test');
        $h = new \ATC\Router('/atc-unit-test2-' . $uniq . '/sub');
        $i = new \ATC\Router('/atc-unit-test2-' . $uniq . '/sub/test');
        $j = new \ATC\Router('/atc-unit-test2-' . $uniq . '/test2');
        $k = new \ATC\Router('/atc-unit-test3-' . $uniq);

        $this->assertEquals('index', $a->tokenGroup);
        $this->assertEquals('index', $b->tokenGroup);
        $this->assertEquals('index', $c->tokenGroup);
        $this->assertEquals('atc-unit-test1-' . $uniq, $d->tokenGroup);
        $this->assertEquals('atc-unit-test2-' . $uniq . '/index', $e->tokenGroup);
        $this->assertEquals('atc-unit-test2-' . $uniq . '/index', $f->tokenGroup);
        $this->assertEquals('atc-unit-test2-' . $uniq . '/test', $g->tokenGroup);
        $this->assertEquals('', $h->tokenGroup);
        $this->assertEquals('atc-unit-test2-' . $uniq . '/sub/test', $i->tokenGroup);
        $this->assertEquals('', $j->tokenGroup);
        $this->assertEquals('atc-unit-test3-' . $uniq, $k->tokenGroup);
    }

    public static function tearDownAfterClass() {
        // cleanup
        foreach (self::$reqFiles as $reqFile) {
            unlink(self::$templateDir . $reqFile);
        }
        foreach (array_reverse(self::$reqDirs) as $reqDir) {
            rmdir(self::$templateDir . $reqDir);
        }
    }
}