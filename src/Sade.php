<?php

namespace Sade;

use Sade\Component\Component;
use Sade\Config\Config;

class Sade extends Container
{
    /**
     * Components directory.
     *
     * @var string
     */
    protected $dir = '';

    /**
     * Current component file.
     *
     * @var string
     */
    protected $file = '';

    /**
     * File directory.
     *
     * @var string
     */
    protected $fileDir = '';

    /**
     * Sade options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Rendered components.
     *
     * @var array
     */
    protected $rendered = [];

    /**
     * Component tags.
     *
     * @var array
     */
    protected $tags = [
        'template',
        'script',
        'style',
    ];

    /**
     * Sade construct.
     *
     * @param string $dir
     * @param array  $options
     */
    public function __construct($dir = '', array $options = [])
    {
        $this->setupDir($dir);
        $this->setupOptions($options);
    }

    /**
     * Get component file with directory.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function file($file)
    {
        if (file_exists(realpath($file)) && strpos($file, $this->fileDir) !== false) {
            return $file;
        }

        $dir = rtrim($this->fileDir, '/') . '/';
        $file = ltrim($file, '/');

        if (strpos($file, $dir) !== false) {
            return $file;
        }

        return realpath($dir . $file);
    }

    /**
     * Set the only type (template, script or style) to include in the rendering.
     *
     * @param  string $type
     *
     * @return string
     */
    public function only($type)
    {
        $types = $this->tags;
        $options = [];

        foreach ($types as $key) {
            if ($key === $type) {
                continue;
            }

            $options[$key] = [
                'enabled' => false
            ];
        }

        return new Sade($this->dir, $options);
    }

    /**
     * Get options.
     *
     * @return \Sade\Config
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Render component file.
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return mixed
     */
    public function render($file, array $data = [])
    {
        if (is_array($file)) {
            $output = '';

            foreach ($file as $item) {
                $output .= $this->render($item) . "\n";
            }

            return $output;
        }

        // Store additional directories.
        $dirs = explode('/', $file);
        $dirs = array_slice($dirs, 0, count($dirs) -1);
        $dirs = implode('/', $dirs);

        if (strpos($dirs, $this->dir) !== false) {
            $this->fileDir = $dirs;
        } else {
            $this->fileDir = implode('/', [$this->dir, $dirs]);
        }

        // Remove any path in file.
        $file = explode('/', $file);
        $file = array_pop($file);

        $filepath = $this->file($file);

        if (!file_exists($filepath)) {
            return;
        }

        // Only render template tag if file already rendered.
        if (!empty($this->rendered[$filepath]['template']) && $this->options['cache']) {
            return $this->rendered[$filepath]['template'];
        }

        $component = new Component($this, $this->fileDir);

        $output = $component->render($file, $data);

        $this->rendered[$filepath] = $output;

        return trim(implode('', array_values($output)));
    }

    /**
     * Setup components directory.
     *
     * @param string $dir
     */
    protected function setupDir($dir)
    {
        $cwd = getcwd();

        if (!is_string($dir) || empty($dir)) {
            $dir = $cwd;
        }

        if (strpos($dir, $cwd) === false) {
            $dir = rtrim($cwd, '/') . '/' . ltrim($dir, '/');
        }

        $this->dir = $this->fileDir = $dir;
    }

    /**
     * Setup options.
     *
     * @param array $options
     */
    protected function setupOptions($options)
    {
        $defaults = [
            'cache'    => true,
            'script'   => [
                'enabled' => true,
            ],
            'style'    => [
                'enabled' => true,
                'scoped'  => false,
                'tag'     => 'script',
            ],
            'template' => [
                'enabled' => true,
                'scoped'  => false
            ],
        ];

        $this->options = new Config(array_replace_recursive($defaults, $options));
    }

    /**
     * Get tags.
     *
     * @return array
     */
    public function tags()
    {
        return $this->tags;
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
        if (in_array($method, $this->tags, true)) {
            return $this->only($method)->render($parameters);
        }
    }
}
