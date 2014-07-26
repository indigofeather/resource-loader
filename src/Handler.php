<?php

namespace Indigofeather\ResourceLoader;

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
