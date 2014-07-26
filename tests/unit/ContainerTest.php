<?php

use Fuel\FileSystem\Finder;
use Indigofeather\ResourceLoader\Container;

class ContainerTest extends \Codeception\TestCase\Test
{
    /**
     * @var Container
     */
    private $container;

    private $paths = [];

    protected function _before()
    {
        $this->container = new Container();
        $this->paths = [
            codecept_root_dir().'resources/foo/',
            codecept_root_dir().'resources/bar/',
        ];
    }

    protected function _after()
    {
        $this->container = null;
    }

    public function testConstruct()
    {
        $this->assertInstanceOf("Indigofeather\\ResourceLoader\\Container", $this->container);
    }

    public function testConstructWithParams()
    {
        $path = [codecept_root_dir().'resources'];
        $finder = new Finder($path);
        $container = new Container($finder, 'json');
        $this->assertEquals('json', $container->getDefaultFormat());
    }

    public function formatProvider()
    {
        return [
            ['json'],
            ['ini'],
            ['yml'],
            ['php'],
        ];
    }

    /**
     * @dataProvider formatProvider
     */
    public function testSetDefaultFormatAndGetDefaultFormat($format)
    {
        $this->container->setDefaultFormat($format);
        $this->assertEquals($format, $this->container->getDefaultFormat());
    }

    public function testSetMultiTimesDefaultFormat()
    {
        $this->container->setDefaultFormat('ini');
        $this->container->setDefaultFormat('php');;
        $this->container->setDefaultFormat('ini');
        $this->assertEquals('ini', $this->container->getDefaultFormat());
    }

    /**
     * @expectedException Exception
     */
    public function testSetDefaultFormatWithNotSupportFormat()
    {
        $this->container->setDefaultFormat('zip');
    }

    public function testAddPath()
    {
        $path = $this->paths[0];
        $this->container->addPath($path);
        $this->assertEquals([$path], $this->container->getPaths());
    }

    public function testAddPaths()
    {
        $paths = $this->paths;
        $this->container->addPaths($paths);
        $this->assertEquals($paths, $this->container->getPaths());
    }

    public function testRemovePath()
    {
        $paths = $this->paths;
        $this->container->addPaths($paths)->removePath(codecept_root_dir().'resources/foo/');
        $this->assertEquals([codecept_root_dir().'resources/bar/'], $this->container->getPaths());
    }

    public function testRemovePaths()
    {
        $paths = $this->paths;
        $this->container->addPaths($paths)->removePaths($paths);
        $this->assertEmpty($this->container->getPaths());
    }

    public function testGetPaths()
    {
        $this->assertEmpty($this->container->getPaths());
    }

    public function testLoad()
    {
        $data = $this->container->setDefaultFormat('yml')
            ->addPath($this->paths[0])
            ->load('date');

        $this->assertArrayHasKey('date', $data);
    }

    public function testLoadTwice()
    {
        $this->container->setDefaultFormat('yml')
            ->addPath($this->paths[0])
            ->load('date');
        $this->assertArrayHasKey('date', $this->container->load('date'));
    }

    public function testLoadNull()
    {
        $this->assertFalse($this->container->load('foo'));
    }

    public function testLoadResourcesInMultiDir()
    {
        $ini = $this->container->addPaths($this->paths)->setDefaultFormat('ini')->load('date');
        $php = $this->container->addPaths($this->paths)->setDefaultFormat('php')->load('date');
        $json = $this->container->addPaths($this->paths)->setDefaultFormat('json')->load('date');
        $this->assertEquals($json, $php);
        $this->assertArrayHasKey('date', $ini);
    }

    public function testGet()
    {
        $this->container->addPaths($this->paths)->setDefaultFormat('yml')->load('date');
        $year = $this->container->get('date')['date']['year'];
        $this->assertEquals(2099, $year);
    }

    public function testGetNull()
    {
        $this->assertNull($this->container->get('foo'));
    }
}
