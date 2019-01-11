<?php

namespace Sade\Container;

use ReflectionClass;
use Sade\Config\Config;

class Container extends Config
{
    /**
     * Bind a resolver to the container.
     *
     * @param  string   $key
     * @param  callable $value
     *
     * @return mixed
     */
    public function bind($key, callable $value)
    {
        parent::set($key, $value);
    }

    /**
     * Resolve the given callable type from the container.
     *
     * @param  string $key
     * @param  array  $args
     *
     * @return mixed
     */
    public function make($key, array $args = [])
    {
        $value = $this->get($key);
        if (empty($value)) {
            return;
        }

        if (is_callable($value)) {
            $func = call_user_func($value, $this);

            if (is_callable($func)) {
                return call_user_func_array($func, $args);
            }
        }

        if (class_exists($value)) {
            $r = new ReflectionClass($value);
            return $r->newInstanceArgs($args);
        }
    }

    /**
     * Set container value.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function set($key, $value = null)
    {
        if (is_callable($value)) {
            $value = function ($sade) use ($value) {
                return $value;
            };
        }

        parent::set($key, $value);
    }

    /**
     * Get a container value.
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
     * Determine if the given container value exists.
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
     * Set a container value.
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
     * Unset a container value.
     *
     * @param  string $key
     */
    public function __unset($key)
    {
        $this->set($key, null);
    }
}
