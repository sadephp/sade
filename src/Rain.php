<?php

namespace Frozzare\Rain;

class Rain
{
    /**
     * Component file.
     *
     * @var string
     */
    protected $file = '';

    /**
     * Rain options.
     *
     * @var array
     */
    protected $options = [
        'dir' => '',
        'style' => [
            'scoped' => false
        ]
    ];

    /**
     * Script value.
     *
     * @var array
     */
    protected $script = [];

    /**
     * Rain construct.
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $this->options = array_merge($this->options, $options);
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
        $script = $this->script($types);

        if (empty($script['components'])) {
            return [];
        }

        if (!is_array($script['components'])) {
            return [];
        }

        $components = [];

        foreach ($script['components'] as $key => $file) {
            if (is_numeric($key)) {
                $key = pathinfo($file, PATHINFO_FILENAME);
            }

            $reg = '/<\s*' . $key . '([^>]*)>(?:(.*?)<\s*\/\s*' . $key .'>|)/';
            $data = [];

            // Extra props attributes.
            if (preg_match_all($reg, $types['template'], $matches)) {
                foreach ($matches[1] as $attribute) {
                    $attribute = str_replace(' /', '', $attribute);
                    $attribute = trim($attribute);

                    if (preg_match('/(.+)\=\"([^"]*)\"/', $attribute, $matches2)) {
                        $data[$matches2[1]] = $matches2[2];
                    }
                }
            }

            $components[$key] = $this->render($file, $data);
        }

        return $components;
    }

    /**
     * Extra from types array.
     *
     * @param  array $types
     *
     * @return mixed
     */
    protected function data($types)
    {
        $data = $this->script($types);
        
        if (empty($data['data'])) {
            return [];
        }

        if (is_array($data['data'])) {
            return $data['data'];
        }

        if (is_callable($data['data'])) {
            $data = $data['data']();
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
        $dir = rtrim($this->options['dir'], '/') . '/';
        
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
     * Render component file.
     *
     * @param  string $file
     * @param  array  $data
     *
     * @return mixed
     */
    public function render($file, array $data = [])
    {
        $this->reset();

        $this->file = $file = $this->file($file);

        if (!file_exists($file)) {
            return;
        }
        
        $contents = file_get_contents($file);

        $types = [
            'template' => '',
            'script' => '',
            'style' => '',
        ];

        $type = '';
        $scoped = $this->options['style']['scoped'];

        // Find template, script and style tags.
        foreach (explode("\n", $contents) as $line) {
            foreach (array_keys($types) as $key) {
                // Find first line with a type tag.
                if (preg_match('/<\s*' . $key . '[^>]*>/', $line)) {
                    $type = $key;

                    // Find scoped style tags.
                    if (!$scoped && preg_match('/\<style(?:.+|)scoped(?:.+|)\>/', $line)) {
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
        $id = $this->id($file);
        $script = $this->script($types);

        // Remove any prop- prefixes.
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $key = str_replace('prop-', '', $key);
            $data[$key] = $value;
        }

        $data = array_merge($this->data($types), $data);

        // Create template html.
        $template = (new Template([
            'content' => $types['template'],
            'id'      => $id,
            'methods' => $script['methods'],
            'scoped'  => $scoped,
        ]))->render($data);

        $template = $this->renderComponents($template, $types);

        // Append style html.
        $template .= (new Style([
            'content' => $types['style'],
            'id'      => $id,
            'scoped'  => $scoped,
        ]))->render();
        
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

    /**
     * Reset properties.
     */
    protected function reset() {
        $this->script = null;
    }

    /**
     * Eval script.
     *
     * @param  array $types
     *
     * @return mixed
     */
    protected function script($types)
    {
        if (!empty($this->script)) {
            return $this->script;
        }

        $result = require $this->file;
        if (empty($result) || !is_array($result)) {
            $script = $types['script'];
            $result = eval($script);

            if (!is_array($result)) {
                $result = [];
            }
        }

        $defaults = [
            'data' => function () {
            },
            'components' => [],
            'methods' => [],
        ];

        $result = array_merge($defaults, $result);

        $this->script = $result;

        return $result;
    }
}
