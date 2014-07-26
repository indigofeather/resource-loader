<?php

namespace Indigofeather\ResourceLoader;

use Symfony\Component\Yaml\Yaml;

class Yml implements Handler
{
    /**
     * {@inheritDoc}
     */
    public function load($file)
    {
        $contents = file_get_contents($file);

        return Yaml::parse($contents);
    }
}
