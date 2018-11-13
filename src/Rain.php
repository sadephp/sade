<?php

namespace Frozzare\Rain;

use Closure;

class Rain
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
     * Rain options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Model value.
     *
     * @var array
     */
    protected $model = [];

    /**
     * Rain construct.
     *
     * @param string $dir
     * @param array  $options
     */
    public function __construct($dir, array $options = [])
    {
        $defaults = [
            'cache'    => [
                'dir'  => '',
                'perm' => ( 0755 & ~ umask() ),
            ],
            'style'    => [
                'scoped' => false
            ],
            'template' => [
                'scoped' => false
            ],
        ];

        $this->dir = is_string($dir) ? $dir : __DIR__;
        $this->options = array_merge($defaults, $options);
        $this->cache = new Cache($this->options['cache']);
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

        if (!is_array($model->components)) {
            return [];
        }

        $components = [];

        foreach ($model->components as $key => $file) {
            if (is_numeric($key)) {
                $key = pathinfo($file, PATHINFO_FILENAME);
            }

            $components[$key] = $this->render($file, [
                'data' => $this->attributes($key, $types['template']),
            ]);
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
        $dir = rtrim($this->dir, '/') . '/';
        $file = ltrim($file, '/');
        
        if (strpos($file, $dir) !== false) {
            return $file;
        }
        
        return $dir . $file;
    }

    /**
     * Generate rain id.
     *
     * @param  string $file
     *
     * @return string
     */
    public function id($file)
    {
        $file = $this->file($file);
        $id = substr(base64_encode($file), 0, 12);

        return 'rain-' . strtolower($id);
    }

    /**
     * Get methods from model file.
     *
     * @param  array $data
     *
     * @return array
     */
    protected function methods(array $data)
    {
        $methods = $this->model->methods;
        $data = (object) $data;

        foreach ($methods as $key => $method) {
            if (!is_callable($method)) {
                unset($methods[$key]);
                continue;
            }

            $methods[$key] = Closure::bind($method, $data);
        }

        return $methods;
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
            'data' => function () {
            },
            'components' => [],
            'methods' => [],
        ];

        // Prepare model data.
        $result = array_merge($defaults, $result);
        $result['data'] = $this->data($result['data']);

        // Prepare extra model data.
        $extra = array_merge($defaults, $extra);
        $result['data'] = array_merge($result['data'], $this->data($extra['data']));

        return (object) $result;
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
        $filepath = $this->file($file);

        if (!file_exists($filepath)) {
            return;
        }

        if ($cache = $this->cache->get($file, filemtime($filepath))) {
            return $cache;
        }

        $this->model = $this->model($file, $model);
        $contents = file_get_contents($filepath);

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
        $scoped = $this->options['style']['scoped'];

        // Extract additional directories.
        $dirs = explode('/', $file);
        $dirs = array_slice($dirs, 0, count($dirs) -1);
        $dirs = implode('/', $dirs);

        // Find template, script and style tags.
        foreach (explode("\n", $contents) as $line) {
            foreach (array_keys($types) as $key) {
                // Find first line with a type tag.
                if (preg_match('/<\s*' . $key . '[^>]*>/', $line)) {
                    $attributes[$key] = $this->attributes($key, $line);
                    $type = $key;

                    // Use src file instead of new line.
                    if (isset($attributes[$key]['src'])) {
                        $type = '';
                        $src = $attributes[$key]['src'];
                        unset($attributes[$key]['src']);

                        // Add additional directories.
                        if (strpos($src, $dirs . '/') === false) {
                            $src = implode('/', [$dirs, $src]);
                        }

                        if (file_exists($this->file($src))) {
                            $types[$key] = file_get_contents($this->file($src));
                        }
                    }

                    // Find scoped style tags.
                    if ($key === 'style' && isset($attributes[$key]['scoped']) && $attributes[$key]['scoped']) {
                        $scoped = true;
                    }

                    continue 2;

                    // Find last line with a type tag.
                } elseif (preg_match('/<\s*\/\s*' . $key . '>/', $line)) {
                    $type = '';
                    continue 2;
                }
            }

            if (empty($type)) {
                continue;
            }

            $types[$type] .= $line;
        }

        // Generate component id.
        $id = $this->id($filepath);
        $data = $this->model->data;

        // Create template html.
        $template = (new Template([
            'attributes' => $attributes['template'],
            'content'    => $types['template'],
            'id'         => $id,
            'methods'    => $this->methods($data),
            'scoped'     => $scoped ? $scoped : $this->options['template']['scoped'],
        ]))->render($data);

        $template = $this->renderComponents($template, $types);

        // Append JavaScript html.
        $template .= (new Script([
            'attributes' => $attributes['script'],
            'content'    => $types['script'],
            'id'         => $id,
        ]))->render();

        // Append style html.
        $template .= (new Style([
            'attributes' => $attributes['style'],
            'content'    => $types['style'],
            'id'         => $id,
            'scoped'     => $scoped,
        ]))->render();

        $template = trim($template);

        $this->cache->set($file, $template);

        return $template;
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
            $template = preg_replace('/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/', $html, $template);
        }

        return $template;
    }
}
