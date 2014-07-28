<?php

namespace Indigofeather\ResourceLoader;

use Symfony\Component\Finder\Finder;
use InvalidArgumentException;
use LogicException;

/**
 * Resource Container
 *
 * @package Indigofeather\ResourceLoader
 */
class Container
{
    /**
     * @var  array $handlers array of resource file handlers
     */
    protected $handlers;

    /**
     * @var  array $paths array of resource paths
     */
    protected $paths = [];

    /**
     * @var  string $defaultFormat default resource format
     */
    protected $defaultFormat = 'php';

    /**
     * @var  array $data cache data
     */
    protected $data = [];

    /**
     * @var  bool $hasAddedPaths must adds paths before load()
     */
    protected $hasAddedPaths = false;

    /**
     * Constructor
     *
     * @param string $defaultFormat
     */
    public function __construct($defaultFormat = 'php')
    {
        $this->defaultFormat = $defaultFormat;
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
    public function has($name)
    {
        $name = $this->ensureDefaultFormat($name);

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

        $finder = new Finder();
        $name = $this->ensureDefaultFormat($name);
        $finder->files()
            ->in($this->paths)
            ->name($name);

        if (! $finder->count()) {
            return false;
        }

        $paths = [];
        foreach ($finder as $file) {
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
     * Get paths
     *
     * @return array paths
     */
    public function getPaths()
    {
        return $this->paths;
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
        $this->paths = array_unique(array_merge($this->paths, $paths));
        $this->hasAddedPaths = true;

        return $this;
    }

    /**
     * Removes a path.
     *
     * @param  string $path path
     * @return $this
     */
    public function removePath($path)
    {
        return $this->removePaths((array) $path);
    }

    /**
     * Removes paths.
     *
     * @param   array $paths paths
     * @return  $this
     */
    public function removePaths(array $paths)
    {
        $this->paths = array_unique(array_diff($this->paths, $paths));
        $this->paths or $this->hasAddedPaths = false;

        return $this;
    }
}
