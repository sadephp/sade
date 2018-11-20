<?php

namespace Sade\Contracts\Component;

interface Tag
{
    /**
     * Template construct.
     *
     * @param array $options
     */
    public function __construct(array $options = []);

    /**
     * Render tag.
     *
     * @return string
     */
    public function render();
}
