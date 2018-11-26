<?php

namespace Sade\Contracts;

interface Sade
{
    /**
     * Sade construct.
     *
     * @param string $dir
     * @param array  $options
     */
    public function __construct($dir = '', array $options = []);

    /**
     * Set the only type (template, script or style) to include in the rendering.
     *
     * @param  string $type
     *
     * @return string
     */
    public function only($type);

    /**
     * Render component.
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return mixed
     */
    public function render($file, array $data = []);
}
