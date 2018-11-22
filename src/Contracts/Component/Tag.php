<?php

namespace Sade\Contracts\Component;

use Sade\Contracts\Sade;

interface Tag
{
    /**
     * Template construct.
     *
     * @param array $options
     */
    public function __construct(array $options, Sade $sade);

    /**
     * Render tag.
     *
     * @return string
     */
    public function render();
}
