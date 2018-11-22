<?php

namespace Sade\Component;

use Closure;
use Sade\Config\Config;
use Sade\Contracts\Sade;

class Options extends Config
{
    /**
     * Sade instance.
     *
     * @var \Sade\Contracts\Sade
     */
    protected $sade = null;

    /**
     * Create new options instance.
     *
     * @param array                $options
     * @param array                $extra
     * @param \Sade\Contracts\Sade $sade
     */
    public function __construct(array $options = [], array $extra = [], $sade = null)
    {
        $defaults = [
            'created'    => function () {
            },
            'components' => [],
            'data'       => function () {
            },
            'filters'    => [],
            'methods'    => [
                'env' => 'env',
            ],
            'props'      => [],
            'scoped'     => false,
        ];

        $this->sade = $sade;

        // Prepare component data.
        $options = array_replace_recursive($defaults, $options);
        $options['data'] = $this->extractData($options['data']);

        // Prepare extra component data.
        $extra = array_replace_recursive($defaults, $extra);
        $options['data'] = array_replace_recursive($options['data'], $this->extractData($extra['data']));

        $options = $this->bindData($options);

        parent::__construct($options);
    }

    /**
     * Set functions on data object for different function types.
     *
     * @param  \Sade\Component\Data $dataobj
     * @param  array                $data
     * @param  string               $name
     */
    protected function setFunctions($dataobj, $data, $name)
    {
        switch ($name) {
            case 'created':
                $this->setFunctions($dataobj, $data, 'filters');
                $this->setFunctions($dataobj, $data, 'methods');
                break;
            case 'filters':
                foreach ($data['methods'] as $key => $value) {
                    $dataobj->set($key, $value);
                }
                break;
            case 'methods':
                foreach ($data['filters'] as $key => $value) {
                    $dataobj->set($key, $value);
                }
                break;
            default:
                break;
        }
    }

    /**
     * Bind data to functions.
     *
     * @param  array $data
     *
     * @return array
     */
    protected function bindData(array $data)
    {
        $dataobj = new Data($this->sade, $data['data']);

        foreach (['created', 'filters', 'methods'] as $name) {
            $this->setFunctions($dataobj, $data, $name);
        }

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

                if (!($func instanceof Closure)) {
                    continue;
                }

                if ($isarr) {
                    $funcs[$key] = Closure::bind($func, $dataobj);
                } else {
                    $funcs[$key] = Closure::bind(function () use ($func) {
                        call_user_func(Closure::bind($func, $this));
                        return (array) $this->items();
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
