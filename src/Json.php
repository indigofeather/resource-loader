<?php

namespace Indigofeather\ResourceLoader;

/**
 * Class Json
 *
 * @package Indigofeather\ResourceLoader
 */
class Json implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        $contents = file_get_contents($file);

        return json_decode($contents, true);
    }
}
