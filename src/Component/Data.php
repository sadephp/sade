<?php

namespace Sade\Component;

use Closure;
use Sade\Container\Container;
use Sade\Contracts\Sade;

class Data extends Container
{
    /**
     * Sade instance.
     *
     * @var \Sade\Contracts\Sade
     */
    protected $sade = null;

    /**
     * Data constructor.
     *
     * @param \Sade\Contracts\Sade $sade
     * @param array                $data
     */
    public function __construct(Sade $sade, array $data = [])
    {
        $this->sade = $sade;

        parent::__construct($data);
    }

    /**
     * Create a new key for callable value.
     *
     * @param  string $key
     *
     * @return string
     */
    protected function callableName($key)
    {
        return sprintf('callable.%s', $key);
    }

    /**
     * Set data value.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function set($key, $value = null)
    {
        if (is_callable($value)) {
            $key = $this->callableName($key);
        }

        parent::set($key,$value);
    }

    /**
     * Call dynamic methods.
     *
     * @param  string $method
     * @param  mixed  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if ($this->has($this->callableName($method))) {
            return $this->make($this->callableName($method), $parameters);
        }

        if ($this->sade->has($method)) {
            return $this->sade->make($method, $parameters);
        }
    }
}
