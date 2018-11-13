<?php

namespace Sade;

use Closure;

class Sade
{
    /**
     * Cache instance.
     */
    protected $cache;

    /**
     * Components directory.
     *
     * @var string
     */
    protected $dir = '';

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
     * Parent values.
     *
     * @var array
     */
    protected $parent = [
        'attributes' => [],
        'model'      => null,
    ];

    /**
     * Model value.
     *
     * @var array
     */
    protected $model = [];

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

        $this->cache = new Cache($this->options->get('cache'));
    }

    /**
     * Extra attributes from html tags.
     *
     * @param  string $key
     * @param  string $content
     *
     * @return array
     */
    protected function attributes($key, $content)
    {
        $reg = '/<\s*' . $key . '([^>]*)>(?:(.*?)<\s*\/\s*' . $key .'>|)/';
        $data = [];

        // Extra props attributes.
        if (preg_match_all($reg, $content, $matches)) {
            foreach ($matches[1] as $attribute) {
                $attribute = str_replace(' /', '', $attribute);
                $attribute = trim($attribute);

                if (preg_match_all('/(?:([^\s=]+)\s*=\s*(\'[^<\']*\'|\"[^<"]*\")|\w+)/', $attribute, $matches2)) {
                    foreach ($matches2[1] as $index => $name) {
                        if (empty($name)) {
                            $name = $matches2[0][$index];
                            $value = '';
                        } else {
                            $value = $matches2[2][$index];
                            $value = substr($value, 1, strlen($value)-2);
                        }

                        $data[$name] = $value;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Bind data to functions.
     *
     * @param  array $funcs
     * @param  array $data
     *
     * @return array
     */
    protected function bindData(array $funcs, array $data)
    {
        $data = (object) $data;

        foreach ($funcs as $key => $func) {
            if (!is_callable($func)) {
                unset($funcs[$key]);
                continue;
            }

            $func[$key] = Closure::bind($func, $data);
        }

        return $funcs;
    }

    /**
     * Determine if cache is enabled or not.
     *
     * @return bool
     */
    protected function cacheEnabled()
    {
        return $this->options->get('template.enabled', true) &&
            $this->options->get('script.enabled', true) &&
            $this->options->get('style.enabled', true);
    }

    /**
     * Render components.
     *
     * @param  array $types
     *
     * @return array
     */
    protected function components($types)
    {
        $model = $this->model;

        if (empty($model->components)) {
            return [];
        }

        if (! is_array($model->components)) {
            return [];
        }

        $components = [];
        $template   = $types['template'];

        foreach ($model->components as $key => $file) {
            if (is_numeric($key)) {
                $key = pathinfo($file, PATHINFO_FILENAME);
            }

            $reg = '/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/is';
            if (!preg_match_all($reg, $template, $matches)) {
                continue;
            }

            $nextModel = $this->model($file);
            $nextData = [];

            // Pass along proprties from parent component if requested.
            foreach ($nextModel->props as $key) {
                if (isset($this->parent['attributes'][$key])) {
                    $nextData[$key] = $this->parent['attributes'][$key];
                }

                if (isset($model->data[$key])) {
                    $nextData[$key] = $model->data[$key];
                }
            }

            // Render components.
            foreach ($matches[0] as $index => $before) {
                $this->parent['attributes'] = $attributes = $this->attributes($key, $before);

                $nextData = array_replace_recursive($nextData, $attributes);

                // Append children values.
                $nextData['children'] = $matches[1][$index];

                // Render child component.
                $components[$before] = $this->render($file, [
                    'data' => $nextData,
                ]);
            }
        }

        return $components;
    }

    /**
     * Get data from data object.
     *
     * @param  mixed $data
     *
     * @return array
     */
    protected function data($data)
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
     * Get component file with directory.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function file($file)
    {
        $dir = rtrim($this->fileDir, '/') . '/';
        $file = ltrim($file, '/');

        if (strpos($file, $dir) !== false) {
            return $file;
        }

        return $dir . $file;
    }

    /**
     * Generate Sade id.
     *
     * @param  string $file
     *
     * @return string
     */
    public function id($file)
    {
        $file = $this->file($file);
        $id = substr(base64_encode($file), 0, 12);

        return 'sade-' . strtolower($id);
    }

    /**
     * Load php model code.
     *
     * @param  string $file
     * @param  array  $extra
     *
     * @return mixed
     */
    protected function model($file, array $extra = [])
    {
        ob_start();
        $result = require $this->file($file);
        ob_end_clean();

        if (!is_array($result)) {
            $result = [];
        }

        $defaults = [
            'components' => [],
            'data'       => function () {
            },
            'filters'    => [],
            'methods'    => [],
            'props'      => [],
        ];

        // Prepare model data.
        $result = array_replace_recursive($defaults, $result);
        $result['data'] = $this->data($result['data']);

        // Prepare extra model data.
        $extra = array_replace_recursive($defaults, $extra);
        $result['data'] = array_replace_recursive($result['data'], $this->data($extra['data']));

        return (object) $result;
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
        $types = ['template', 'script', 'style'];
        $options = [];

        foreach ($types as $key) {
            if ($key === $type) {
                continue;
            }

            $options[$key] = [
                'enabled' => false
            ];
        }

        return $this->options($options);
    }

    /**
     * Set options.
     *
     * @param  array $options
     *
     * @return \Sade\Sade
     */
    public function options(array $options)
    {
        return new Sade($this->dir, $options);
    }

    /**
     * Parse file content and extract attributes and types.
     *
     * @param  string $contents
     *
     * @return array
     */
    protected function parseFileContent($contents)
    {
        $types = [
            'template' => '',
            'script'   => '',
            'style'    => '',
        ];

        $attributes = [
            'template' => [],
            'script'   => [],
            'style'    => [],
        ];

        $type = '';
        // Regex for testing if a src starts with // or http[s]://.
        $urlStartReg = '/^(?:\/\/|(http(s?))\:\/\/)/';

        // Find template, script and style tags.
        foreach (array_keys($types) as $key) {
            $reg = '/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/is';

            if (!preg_match($reg, $contents, $matches)) {
                continue;
            }

            $attributes[$key] = $this->attributes($key, $matches[0]);

            // Load source file if found.
            if (count($matches) === 1 || empty($matches[1])) {
                if (isset($attributes[$key]['src'])) {
                    $src = $attributes[$key]['src'];

                    if (preg_match($urlStartReg, $src)) {
                        continue;
                    }

                    unset($attributes[$key]['src']);

                    if (file_exists($this->file($src))) {
                        $types[$key] = file_get_contents($this->file($src));
                    }
                }
            } else {
                $types[$key] = $matches[1];
            }
        }

        return [$attributes, $types];
    }

    /**
     * Render component file.
     *
     * @param  string $file
     * @param  array  $model
     *
     * @return mixed
     */
    public function render($file, array $model = [])
    {
        // Store additional directories.
        $dirs = explode('/', $file);
        $dirs = array_slice($dirs, 0, count($dirs) -1);
        $dirs = implode('/', $dirs);
        $this->fileDir = implode('/', [$this->dir, $dirs]);

        // Remove any path in file.
        $file = explode('/', $file);
        $file = array_pop($file);

        $filepath = $this->file($file);

        if (!file_exists($filepath)) {
            return;
        }

        // Try using the cache value.
        if ($this->cacheEnabled()) {
            if ($cache = $this->cache->get($file, filemtime($filepath))) {
                return $cache;
            }
        }

        $this->model = $this->parent['model'] = $this->model($file, $model);

        // Find attributes and types.
        list($attributes, $types) = $this->parseFileContent(file_get_contents($filepath));

        // Generate component id.
        $id = $this->id($filepath);

        // Global options.
        $scoped = isset($attributes['style']['scoped']) ? true : $this->options->get('style.scoped', false);
        $template = '';

        // Append template html if enabled.
        if ($this->options->get('template.enabled', true)) {
            $data = $this->model->data;
            $template .= (new Template([
                'attributes' => $attributes['template'],
                'content'    => $types['template'],
                'filters'    => $this->bindData($this->model->filters, $data),
                'id'         => $id,
                'methods'    => $this->bindData($this->model->methods, $data),
                'scoped'     => $scoped ? $scoped : $this->options->get('template.scoped', false),
            ]))->render($data);

            $template = $this->renderComponents($template, $types);
        }

        // Append JavaScript html if enabled.
        if ($this->options->get('script.enabled', true)) {
            $template .= (new Script([
                'attributes' => $attributes['script'],
                'content'    => $types['script'],
                'id'         => $id,
                'scoped'     => $scoped ? $scoped : $this->options->get('script.scoped', false),
            ]))->render();
        }

        // Append style html if enabled.
        if ($this->options->get('style.enabled', true)) {
            $template .= (new Style([
                'attributes' => $attributes['style'],
                'content'    => $types['style'],
                'id'         => $id,
                'scoped'     => $scoped,
            ]))->render();
        }

        if ($this->cacheEnabled()) {
            return $this->cache->set($file, trim($template));
        }

        return trim($template);
    }

    /**
     * Render components.
     *
     * @param  string $template
     * @param  array  $types
     *
     * @return string
     */
    protected function renderComponents($template, $types)
    {
        $types['template'] = $template;

        $components = $this->components($types);

        // Replace components tags.
        foreach ($components as $key => $html) {
            $template = str_replace($key, $html, $template);
        }

        return $template;
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
            'cache'    => [
                'dir'  => '',
                'perm' => ( 0755 & ~ umask() ),
            ],
            'script'   => [
                'enabled' => true,
            ],
            'style'    => [
                'enabled' => true,
                'scoped'  => false
            ],
            'template' => [
                'enabled' => true,
                'scoped'  => false
            ],
        ];

        $this->options = new Config(array_replace_recursive($defaults, $options));
    }
}
