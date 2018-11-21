<?php

namespace Sade\Component;

use Hashids\Hashids;
use Sade\Contracts\Sade;

class Component
{
    /**
     * Component directory.
     *
     * @var string
     */
    protected $dir = '';

    /**
     * Component options.
     *
     * @var \Sade\Options
     */
    protected $options = null;

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
     * Sade instance.
     *
     * @var \Sade\Contracts\Sade
     */
    protected $sade = null;

    /**
     * Component construct.
     *
     * @param  \Sade\Contracts\Sade $sade
     * @param  string               $dir
     */
    public function __construct(Sade $sade, $dir)
    {
        $this->sade = $sade;
        $this->dir = $dir;
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

        if (!preg_match_all($reg, $content, $matches)) {
            return $data;
        }

        foreach ($matches[1] as $attribute) {
            $attribute = str_replace(' /', '', $attribute);
            $attribute = trim($attribute);

            if (!preg_match_all('/(?:([^\s=]+)\s*=\s*(\'[^<\']*\'|\"[^<"]*\")|\w+)/', $attribute, $matches2)) {
                continue;
            }

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

        return $data;
    }

    /**
     * Generate Sade classname.
     *
     * @param  string $file
     *
     * @return string
     */
    public function className($file)
    {
        $file = $this->file($file);
        $hashids = new Hashids($file);
        $hash = $hashids->encode(1, 2, 3);

        return 'sade-' . strtolower($hash);
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
        $options = $this->options;

        if (empty($options->components)) {
            return [];
        }

        if (! is_array($options->components)) {
            return [];
        }

        $components = [];
        $template   = $types['template'];

        foreach ($options->components as $key => $file) {
            if (is_numeric($key)) {
                $key = pathinfo($file, PATHINFO_FILENAME);
            }

            $reg = '/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/is';
            if (! preg_match_all($reg, $template, $matches)) {
                continue;
            }

            $nextData = [];
            $nextOptions = $this->options($file);

            // Pass along proprties from parent component if requested.
            foreach (array_unique($nextOptions->props) as $key) {
                if (isset($this->parent['attributes'], $this->parent['attributes'][$key])) {
                    $nextData[$key] = $this->parent['attributes'][$key];
                }

                if (isset($this->parent['options'], $this->parent['options']->data[$key])) {
                    $nextData[$key] = $this->parent['options']->data[$key];
                }

                if (isset($options->data[$key])) {
                    $nextData[$key] = $options->data[$key];
                }
            }

            // Render components.
            foreach ($matches[0] as $index => $before) {
                $this->parent['attributes'] = $attributes = $this->attributes($key, $before);

                $nextData = array_replace_recursive($nextData, $attributes);

                // Render children value since it may contains twig code.
                $children = $matches[1][$index];
                $children = (new Template([
                    'component' => $options,
                    'content'   => $children,
                ]))->render();

                // Append children value to next component data.
                $nextData['children'] = $children;

                // Render child component.
                $components[$reg] = $this->render($file, [
                    'data' => $nextData,
                ]);
            }
        }

        return $components;
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
        if (file_exists(realpath($file)) && strpos($file, $this->dir) !== false) {
            return $file;
        }

        $dir = rtrim($this->dir, '/') . '/';
        $file = ltrim($file, '/');

        if (strpos($file, $dir) !== false) {
            return $file;
        }

        return realpath($dir . $file);
    }

    /**
     * Load component options.
     *
     * @param  string $file
     * @param  array  $extra
     *
     * @return mixed
     */
    protected function options($file, array $extra = [])
    {
        if (!$this->sade->has($file)) {
            ob_start();
            $result = require $this->file($file);
            ob_end_clean();
            $this->sade->set($file, $result);
        }

        $result = $this->sade->get($file);
        if (!is_array($result)) {
            $result = [];
            $this->sade->set($file, null);
        }

        return new Options($result, $extra, $this->sade);
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

        foreach ($this->sade->tags() as $tag) {
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
     * @param  array  $options
     *
     * @return array
     */
    public function render($file, array $options = [])
    {
        $this->file = $filepath = $this->file($file);

        if (!file_exists($filepath)) {
            return [];
        }

        $this->options = $this->parent['options'] = $this->options($file, $options);
        $className = $this->className($filepath);

        // Find attributes and types.
        list($attributes, $types) = $this->parseFileContent(file_get_contents($filepath));

        // Call created function right before component will be created.
        if ($newData = call_user_func($this->options->get('created'))) {
            $this->options->set('data', $newData);
        }

        $output = [];

        // Render template, script and style tags.
        foreach ($this->sade->tags() as $tag) {
            $enabled = $this->sade->option(sprintf('%s.enabled', $tag), true);
            if ($enabled) {
                $func = [$this, 'render' . ucfirst($tag)];
                $res = call_user_func_array($func, [$className, $attributes, $types]);
                $output[$tag] = trim($res);
            }
        }

        $components = $this->components($types);

        // Append all components in the right order.
        foreach ($components as $reg => $component) {
            foreach ($this->sade->tags() as $tag) {
                if ($tag === 'template') {
                    $output[$tag] = preg_replace($reg, $component[$tag], $output[$tag]);
                } else {
                    $output[$tag] .= $component[$tag];
                }
            }
        }

        return $output;
    }

    /**
     * Render template tag.
     *
     * @param  string $className
     * @param  array  $attributes
     * @param  array  $types
     *
     * @return string
     */
    protected function renderTemplate($className, $attributes, $types)
    {
        $class = $this->sade->option('template.class');

        if (!class_exists($class)) {
            return '';
        }

        return (new $class([
            'attributes' => $attributes['template'],
            'component'  => $this->options,
            'content'    => $types['template'],
            'class'      => $className,
            'file'       => basename($this->file),
            'scoped'     => $this->scopedTemplateOrScript($attributes),
        ]))->render();
    }

    /**
     * Render script tag.
     *
     * @param  string $className
     * @param  array  $attributes
     * @param  array  $types
     *
     * @return string
     */
    protected function renderScript($className, $attributes, $types)
    {
        $class = $this->sade->option('script.class');

        if (!class_exists($class)) {
            return '';
        }

        return (new $class([
            'attributes'    => $attributes['script'],
            'component'     => $this->options,
            'content'       => $types['script'],
            'class'         => $className,
            'scoped'        => $this->scopedTemplateOrScript($attributes),
            'templateClass' => $this->sade->option('template.class'),
        ]))->render();
    }

    /**
     * Render style tag.
     *
     * @param  string $className
     * @param  array  $attributes
     * @param  array  $types
     *
     * @return string
     */
    protected function renderStyle($className, $attributes, $types)
    {
        $class = $this->sade->option('style.class');

        if (!class_exists($class)) {
            return '';
        }

        return (new $class([
            'attributes'    => $attributes['style'],
            'component'     => $this->options,
            'content'       => $types['style'],
            'class'         => $className,
            'scoped'        => $this->scopedStyle($attributes),
            'tag'           => $this->sade->option('style.tag', 'script'),
            'templateClass' => $this->sade->option('template.class'),
        ]))->render();
    }

    /**
     * Determine if attributes contains a scoped style tag or is scoped by default.
     *
     * @param  array $attributes
     *
     * @return bool
     */
    protected function scopedStyle($attributes)
    {
        if (isset($attributes['style']['scoped'])) {
            return true;
        }

        return $this->sade->option('style.scoped');
    }

    /**
     * Determine if attributes contains a scoped template or script tag or is scoped by default.
     *
     * @param  array $attributes
     *
     * @return bool
     */
    protected function scopedTemplateOrScript($attributes)
    {
        if ($this->scopedStyle($attributes)) {
            return true;
        }

        if (isset($attributes['script']['scoped'])) {
            return true;
        }

        if ($this->sade->option('script.scoped')) {
            return true;
        }

        if (isset($attributes['template']['scoped'])) {
            return true;
        }

        if ($this->sade->option('template.scoped')) {
            return true;
        }

        return false;
    }
}
