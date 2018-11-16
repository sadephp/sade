<?php

namespace Sade;

use Closure;
use Hashids\Hashids;

class Sade
{
    /**
     * Component data.
     *
     * @var array
     */
    protected $data = null;

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
        'data'       => null,
    ];

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
        $data = $this->data;

        if (empty($data->components)) {
            return [];
        }

        if (! is_array($data->components)) {
            return [];
        }

        $components = [];
        $template   = $types['template'];

        foreach ($data->components as $key => $file) {
            if (is_numeric($key)) {
                $key = pathinfo($file, PATHINFO_FILENAME);
            }

            $reg = '/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/is';
            if (! preg_match_all($reg, $template, $matches)) {
                continue;
            }

            $nextData = [];

            // Pass along proprties from parent component if requested.
            foreach ($this->data($file)->props as $key) {
                if (isset($this->parent['attributes'][$key])) {
                    $nextData[$key] = $this->parent['attributes'][$key];
                }

                if (isset($data->data[$key])) {
                    $nextData[$key] = $data->data[$key];
                }
            }

            // Render components.
            foreach ($matches[0] as $index => $before) {
                $this->parent['attributes'] = $attributes = $this->attributes($key, $before);

                $nextData = array_replace_recursive($nextData, $attributes);

                // Append children values.
                $nextData['children'] = $matches[1][$index];

                // Render child component.
                $components[$before] = $this->preRender($file, [
                    'data' => $nextData,
                ]);
            }
        }

        return $components;
    }

    /**
     * Load component data.
     *
     * @param  string $file
     * @param  array  $extra
     *
     * @return mixed
     */
    protected function data($file, array $extra = [])
    {
        ob_start();
        $result = require $this->file($file);
        ob_end_clean();

        if (! is_array($result)) {
            $result = [];
        }

        return new Data($result, $extra);
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
        if (file_exists(realpath($file))) {
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
     * Generate Sade id.
     *
     * @param  string $file
     *
     * @return string
     */
    public function id($file)
    {
        $file = $this->file($file);
        $hashids = new Hashids($file);
        $id = $hashids->encode(1, 2, 3);

        return 'sade-' . strtolower($id);
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
        $attributes = $types = [];

        foreach ($this->tags as $tag) {
            $attributes[$tag] = [];
            $types[$tag] = '';
        }

        // Regex for testing if a src starts with // or http[s]://.
        $urlStartReg = '/^(?:\/\/|(http(s?))\:\/\/)/';

        // Find template, script and style tags.
        foreach (array_keys($types) as $key) {
            $reg = '/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/is';

            if (! preg_match($reg, $contents, $matches)) {
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
     * Pre render file and return array of type tags.
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return array
     */
    protected function preRender($file, array $data = [])
    {
        $filepath = $this->file($file);

        if (!file_exists($filepath)) {
            return [];
        }

        $this->data = $this->parent['data'] = $this->data($file, $data);
        $id = $this->id($filepath);

        // Find attributes and types.
        list($attributes, $types) = $this->parseFileContent(file_get_contents($filepath));

        // Call created function right before component will be created.
        if ($newData = call_user_func($this->data->get('created'))) {
            $this->data->set('data', $newData);
        }

        $output = [];

        // Render template, script and style tags.
        foreach ($this->tags as $tag) {
            $enabled = $this->options->get(sprintf('%s.enabled', $tag), true);
            if ($enabled) {
                $func = [$this, 'render' . ucfirst($tag)];
                $res = call_user_func_array($func, [$id, $attributes, $types]);
                $output[$tag] = trim($res);
            }
        }

        $components = $this->components($types);

        // Append all components in the right order.
        foreach ($components as $key => $component) {
            foreach ($this->tags as $tag) {
                if ($tag === 'template') {
                    $output[$tag] = str_replace($key, $component[$tag], $output[$tag]);
                } else {
                    $output[$tag] .= $component[$tag];
                }
            }
        }

        $this->rendered[$filepath] = $output;

        return $output;
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

        $output = $this->preRender($file, $data);

        return trim(implode('', array_values($output)));
    }

    /**
     * Render template tag.
     *
     * @param  string $id
     * @param  array  $attributes
     * @param  array  $types
     *
     * @return string
     */
    protected function renderTemplate($id, $attributes, $types)
    {
        $scoped = $this->scoped($attributes);

        return (new Template([
            'attributes' => $attributes['template'],
            'content'    => $types['template'],
            'data'       => $this->data,
            'id'         => $id,
            'scoped'     => $scoped ? $scoped : $this->options->get('template.scoped', false),
        ]))->render();
    }

    /**
     * Render script tag.
     *
     * @param  string $id
     * @param  array  $attributes
     * @param  array  $types
     *
     * @return string
     */
    protected function renderScript($id, $attributes, $types)
    {
        $scoped = $this->scoped($attributes);

        return (new Script([
            'attributes' => $attributes['script'],
            'content'    => $types['script'],
            'data'       => $this->data,
            'id'         => $id,
            'scoped'     => $scoped ? $scoped : $this->options->get('script.scoped', false),
        ]))->render();
    }

    /**
     * Render style tag.
     *
     * @param  string $id
     * @param  array  $attributes
     * @param  array  $types
     *
     * @return string
     */
    protected function renderStyle($id, $attributes, $types)
    {
        return (new Style([
            'attributes' => $attributes['style'],
            'content'    => $types['style'],
            'data'       => $this->data,
            'id'         => $id,
            'scoped'     => $this->scoped($attributes),
            'tag'        => $this->options->get('style.tag', 'script'),
        ]))->render();
    }

    /**
     * Test if attributes contains a scoped style tag or is scoped by default.
     *
     * @param  array $attributes
     *
     * @return bool
     */
    protected function scoped($attributes)
    {
        return isset($attributes['style']['scoped']) ? true : $this->options->get('style.scoped', false);
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
