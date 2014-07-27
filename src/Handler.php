<?php

namespace Indigofeather\ResourceLoader;

/**
 * Interface Handler
 *
 * @package Indigofeather\ResourceLoader
 * @codeCoverageIgnore
 */
interface Handler
{
    /**
     * Load a resource file
     *
     * @param  string $file file path
     * @return array  resource contents
     */
    public function load($file);
}
