<?php

namespace Indigofeather\ResourceLoader;

class Php implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        return include $file;
    }
}
