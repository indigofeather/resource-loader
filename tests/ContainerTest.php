<?php
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Indigofeather\ResourceLoader\Container;

class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    private $paths = [];

    protected function setUp()
    {
        $this->container = new Container();
        $this->paths = [
            __DIR__.'/../resources/foo/',
            __DIR__.'/../resources/bar/',
        ];
    }

    protected function tearDown()
    {
        $this->container = null;
    }

    public function testConstruct()
    {
        $this->assertInstanceOf("Indigofeather\\ResourceLoader\\Container", $this->container);
    }

    public function testConstructWithParams()
    {
        $container = new Container('json');
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
        $this->container->setDefaultFormat('php');
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
    public function testLoadWrongPath()
    {
        $this->container->addPath('path/to/aa')->load('aaa');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testLoadWrongPaths()
    {
        $this->container->addPaths(['path/to/aa', 'path/to/bb'])->load('aaa');
    }

    /**
     * @dataProvider formatProvider
     * @param $format
     */
    public function testLoad($format)
    {
        $data = $this->container->setDefaultFormat($format)
            ->addPaths($this->paths)
            ->load('date');

        $this->assertArrayHasKey('date', $data);
    }

    public function testLoadTwice()
    {
        $this->container->setDefaultFormat('ini')
            ->addPath($this->paths[1])
            ->load('date');

        $this->container->load('date');
        $this->assertTrue($this->container->has('date'));
    }

    public function testHas()
    {
        $this->container->setDefaultFormat('yml')
            ->addPath($this->paths[0])
            ->load('date');
        $this->assertTrue($this->container->has('date'));
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

    public function testAddPath()
    {
        $this->container->addPath($this->paths[0]);
        $this->assertCount(1, $this->container->getPaths());
    }

    public function testAddPaths()
    {
        $this->container->addPaths($this->paths);
        $this->assertCount(2, $this->container->getPaths());
    }

    public function testRemovePath()
    {
        $this->container->addPaths($this->paths)
            ->removePath($this->paths[0]);

        $this->assertCount(1, $this->container->getPaths());
    }

    public function testRemovePaths()
    {
        $this->container->addPaths($this->paths)
            ->removePaths($this->paths);

        $this->assertCount(0, $this->container->getPaths());
    }

    public function testGetPaths()
    {
        $this->assertInternalType('array', $this->container->getPaths());
    }
}
