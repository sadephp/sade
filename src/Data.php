<?php

namespace Sade;

class Data extends Config
{
    /**
     * Sade instance.
     *
     * @var \Sade\Sade
     */
    protected $sade = null;

    /**
     * Data constructor.
     *
     * @param \Sade\Sade $sade
     * @param array      $data
     */
    public function __construct($sade, array $data = [])
    {
        $this->sade = $sade;

        parent::__construct($data);
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
        if ($this->sade->has($method)) {
            return $this->sade->make($method, $parameters);
        }
    }

    /**
     * Get a data value.
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Determine if the given data value exists.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->has($key);
    }

    /**
     * Set a data value.
     *
     * @param  string $key
     *
     * @param  mixed  $value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a data value.
     *
     * @param  string $key
     */
    public function __unset($key)
    {
        $this->set($key, null);
    }
}
