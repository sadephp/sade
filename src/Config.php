<?php

namespace Sade;

use ArrayAccess;

class Config implements ArrayAccess
{
    /**
     * All of the configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new configuration.
     *
     * @param  array  $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Get configuration items.
     *
     * @return array
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Determine if a configuration has a value.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function has($key)
    {
        $items = $this->items;

        if (isset($items[$key])) {
            return true;
        }

        foreach (explode('.', $key) as $part) {
            if (! is_array($items) or ! isset($items[$part])) {
                return false;
            }

            $items = $items[$part];
        }

        return true;
    }

    /**
     * Get configuration value.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $items = $this->items;

        if (isset($items[$key])) {
            return $items[$key] ?? $default;
        }

        foreach (explode('.', $key) as $part) {
            if (! is_array($items) or ! isset($items[$part])) {
                return $default;
            }

            $items = $items[$part];
        }

        return $items ?? $default;
    }

    /**
     * Set configuration value.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            $items = &$this->items;
            $parts = explode('.', $key);

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (! isset($items[$part]) or ! is_array($items[$part])) {
                    $array[$part] = [];
                }

                $items = &$items[$part];
            }

            $name = array_shift($parts);

            if (isset($items[$name]) && is_array($items[$name])) {
                $items[$name] = array_replace_recursive($items[$name], $value);
            } else {
                $items[$name] = $value;
            }
        }
    }

    /**
     * Determine if the given configuration option exists.
     *
     * @param  string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get a configuration option.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set a configuration option.
     *
     * @param  string $key
     *
     * @param  mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset a configuration option.
     *
     * @param  string $key
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
