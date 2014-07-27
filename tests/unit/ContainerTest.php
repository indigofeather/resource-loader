<?php

use Symfony\Component\Finder\Finder;
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
        $finder = new Finder();
        $container = new Container($finder->in($path), 'json');
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
     * @param $format
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
     * @expectedException InvalidArgumentException
     */
    public function testSetDefaultFormatWithNotSupportFormat()
    {
        $this->container->setDefaultFormat('zip');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddWrongPath()
    {
        $this->container->addPath('path/to/aa');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddWrongPaths()
    {
        $this->container->addPaths(['path/to/aa', 'path/to/bb']);
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
        $this->container->addPaths($this->paths);
        $this->assertFalse($this->container->load('foo'));
    }

    /**
     * @expectedException LogicException
     */
    public function testLoadWithoutAddPath()
    {
        $this->assertFalse($this->container->load('foo'));
    }

    public function testLoadResourcesInMultiDir()
    {
        $this->container->addPaths($this->paths);
        $json = $this->container->setDefaultFormat('json')->load('date');
        $php = $this->container->setDefaultFormat('php')->load('date');
        $ini = $this->container->setDefaultFormat('ini')->load('date');
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

    public function testGetFinder()
    {
        $this->assertInstanceOf("Symfony\\Component\\Finder\\Finder", $this->container->getFinder());
    }
}
