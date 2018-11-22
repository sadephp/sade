<?php

namespace Sade\Component;

use Sade\Bridges\Node;
use Sade\Contracts\Component\Tag as TagContract;
use Sade\Contracts\Sade;

abstract class Tag implements TagContract
{
    /**
     * Tag options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Sade instance.
     *
     * @var \Sade\Contracts\Sade
     */
    protected $sade = null;

    /**
     * Tag construct.
     *
     * @param array                $options
     * @param \Sade\Contracts\Sade $sade
     */
    public function __construct(array $options, Sade $sade)
    {
        $this->options = array_merge($this->options, $options);
        $this->sade = $sade;
    }

    /**
     * Render tag html.
     *
     * @return string
     */
    abstract public function render();
}
