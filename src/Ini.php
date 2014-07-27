<?php

namespace Indigofeather\ResourceLoader;

/**
 * Class Ini
 *
 * @package Indigofeather\ResourceLoader
 */
class Ini implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        $contents = file_get_contents($file);

        return parse_ini_string($contents, true);
    }
}
