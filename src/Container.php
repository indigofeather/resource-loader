<?php

namespace Indigofeather\ResourceLoader;

use Fuel\FileSystem\Finder;
use Exception;

class Container
{
    /**
     * @var  \Fuel\FileSystem\Finder  $finder  resource finder
     */
    protected $finder;

    /**
     * @var  array  $handlers  array of resource file handlers
     */
    protected $handlers;

    /**
     * @var  string  $defaultFormat  default resource format
     */
    protected $defaultFormat = 'php';

    /**
     * @var  array  $data  cache data
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param Finder   $finder
     * @param string $defaultFormat
     */
    public function __construct($finder = null, $defaultFormat = 'php')
    {
        if (! $finder) {
            $finder = new Finder();
        }

        $this->defaultFormat = $defaultFormat;
        $this->finder = $finder;
    }

    /**
     * Get a value from this container's data
     *
     * @param string  $name
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
     * @param string  $name
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
     * @throws \Exception
     */
    public function load($name)
    {
        if ($cached = $this->get($name)) {
            return $cached;
        }

        $name = $this->ensureDefaultFormat($name);
        $paths = $this->finder->findAllFiles($name);

        if (empty($paths)) {
            return false;
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
     * @param   string  $format  default format
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

        return empty($this->configFolder) ? $file : $this->configFolder.DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Retrieve the handler for a file type
     *
     * @param   string   $extension  extension
     * @return  Handler  file handler
     * @throws Exception
     */
    protected function getHandler($extension)
    {
        if (isset($this->handlers[$extension])) {
            return $this->handlers[$extension];
        }

        $class = 'Indigofeather\ResourceLoader\\'.ucfirst($extension);

        if (! class_exists($class, true)) {
            throw new Exception('Could not find config handler for extension: '.$extension);
        }

        $handler = new $class;
        $this->handlers[$extension] = $handler;

        return $handler;
    }

    /**
     * Add a path.
     *
     * @param   array  $path  path
     * @return  $this
     */
    public function addPath($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $this->finder->addPath($path);

        return $this;
    }

    /**
     * Adds paths to look in.
     *
     * @param array $paths paths
     * @return  $this
     */
    public function addPaths(array $paths)
    {
        array_map([$this, 'addPath'], $paths);

        return $this;
    }

    /**
     * Remove a path.
     *
     * @param   array  $path  path
     * @return  $this
     */
    public function removePath($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $this->finder->removePath($path);

        return $this;
    }

    /**
     * Remove paths.
     *
     * @param   array  $paths  paths
     * @return  $this
     */
    public function removePaths(array $paths)
    {
        array_map([$this, 'removePath'], $paths);

        return $this;
    }

    /**
     * Get Paths.
     *
     * @return array  paths
     */
    public function getPaths()
    {
        return $this->finder->getPaths();
    }
}
