<?php

namespace Indigofeather\ResourceLoader;

use Symfony\Component\Finder\Finder;
use InvalidArgumentException;
use LogicException;

/**
 * Class Container
 *
 * @package Indigofeather\ResourceLoader
 */
class Container
{
    /**
     * @var  \Symfony\Component\Finder\Finder $finder resource finder
     */
    protected $finder;

    /**
     * @var  array $handlers array of resource file handlers
     */
    protected $handlers;

    /**
     * @var  string $defaultFormat default resource format
     */
    protected $defaultFormat = 'php';

    /**
     * @var  array $data cache data
     */
    protected $data = [];

    /**
     * @var bool $hasAddedPaths must adds paths before load()
     */
    protected $hasAddedPaths = false;

    /**
     * Constructor
     *
     * @param Finder $finder
     * @param string $defaultFormat
     */
    public function __construct(Finder $finder = null, $defaultFormat = 'php')
    {
        if (! $finder) {
            $finder = new Finder();
        }

        $this->defaultFormat = $defaultFormat;
        $this->finder = $finder->files();
    }

    /**
     * Get a value from this container's data
     *
     * @param string $name
     * @return array|null
     */
    public function get($name)
    {
        $name = $this->ensureDefaultFormat($name);

        return $this->has($name) ? $this->data[$name] : null;
    }

    /**
     * Check if a resource was set upon this container's data
     *
     * @param string $name
     * @return bool
     */
    protected function has($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Load a resource
     *
     * @param $name
     * @return array
     * @throws LogicException
     */
    public function load($name)
    {
        if ($this->hasAddedPaths !== true) {
            throw new LogicException('You must call one of addPath() or addPaths() methods before load().');
        }

        if ($cached = $this->get($name)) {
            return $cached;
        }

        $name = $this->ensureDefaultFormat($name);
        $this->finder->name($name);

        if (! $this->finder->count()) {
            return false;
        }

        $paths = [];
        foreach ($this->finder as $file) {
            $paths[] = $file->getRealpath();
        }

        $path = end($paths);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $handler = $this->getHandler($extension);
        $resource = $handler->load($path);

        $this->data[$name] = $resource;

        return $resource;
    }

    /**
     * Get Finder
     *
     * @return Finder
     */
    public function getFinder()
    {
        return $this->finder;
    }

    /**
     * Set the default format
     *
     * @param   string $format default format
     * @return  $this
     */
    public function setDefaultFormat($format)
    {
        $this->getHandler($format);
        $this->defaultFormat = $format;

        return $this;
    }

    /**
     * Get the default format
     *
     * @return  string  the default format
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * Ensure a default resource format.
     *
     * @param  string $file resource file name
     * @return string file name with ensured extension
     */
    protected function ensureDefaultFormat($file)
    {
        if (! pathinfo($file, PATHINFO_EXTENSION)) {
            $file .= '.'.$this->defaultFormat;
        }

        return $file;
    }

    /**
     * Retrieve the handler for a file type
     *
     * @param   string $extension extension
     * @return  Handler  file handler
     * @throws InvalidArgumentException
     */
    protected function getHandler($extension)
    {
        if (isset($this->handlers[$extension])) {
            return $this->handlers[$extension];
        }

        $class = 'Indigofeather\ResourceLoader\\'.ucfirst($extension);

        if (! class_exists($class, true)) {
            throw new InvalidArgumentException('Could not find config handler for extension: '.$extension);
        }

        $handler = new $class;
        $this->handlers[$extension] = $handler;

        return $handler;
    }

    /**
     * Adds a path.
     *
     * @param  string $path path
     * @return $this
     */
    public function addPath($path)
    {
        return $this->addPaths((array) $path);
    }

    /**
     * Adds paths to look in.
     *
     * @param   array $paths paths
     * @return  $this
     */
    public function addPaths(array $paths)
    {
        $this->finder->in($paths);
        $this->hasAddedPaths = true;

        return $this;
    }
}
