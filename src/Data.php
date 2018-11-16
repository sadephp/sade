<?php

namespace Sade;

use Closure;

class Data extends Config
{
    /**
     * Create a new data.
     *
     * @param array $data
     * @param array $extra
     */
    public function __construct(array $data = [], array $extra = [])
    {
        $defaults = [
            'created'    => function () {
            },
            'components' => [],
            'data'       => function () {
            },
            'filters'    => [],
            'methods'    => [],
            'props'      => [],
        ];

        // Prepare component data.
        $data = array_replace_recursive($defaults, $data);
        $data['data'] = $this->extractData($data['data']);

        // Prepare extra component data.
        $extra = array_replace_recursive($defaults, $extra);
        $data['data'] = array_replace_recursive($data['data'], $this->extractData($extra['data']));

        $data = $this->bindData($data);

        parent::__construct($data);
    }

    /**
     * Bind data to functions.
     *
     * @param  array $funcs
     * @param  array $data
     *
     * @return array
     */
    protected function bindData(array $data)
    {
        $dataobj = (object) $data['data'];

        foreach (['created', 'filters', 'methods'] as $name) {
            $funcs = $data[$name];

            $isarr = is_array($funcs);

            if (!$isarr) {
                $funcs = [$funcs];
            }

            foreach ($funcs as $key => $func) {
                if (!is_callable($func)) {
                    unset($funcs[$key]);
                    continue;
                }

                if ($isarr) {
                    $funcs[$key] = Closure::bind($func, $dataobj);
                } else {
                    $funcs[$key] = Closure::bind(function () use ($func) {
                        call_user_func(Closure::bind($func, $this));
                        return (array) $this;
                    }, $dataobj);
                }
            }

            if (!$isarr) {
                $funcs = $funcs[0];
            }

            $data[$name] = $funcs;
        }

        return $data;
    }

    /**
     * Extract data from data value.
     *
     * @param  mixed $data
     *
     * @return array
     */
    protected function extractData($data)
    {
        if (empty($data)) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        if (is_callable($data)) {
            $data = call_user_func($data);
        }

        if (is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * Get a configuration option.
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
     * Determine if the given configuration option exists.
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
     * Set a configuration option.
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
     * Unset a configuration option.
     *
     * @param  string $key
     */
    public function __unset($key)
    {
        $this->set($key, null);
    }
}
