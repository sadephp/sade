<?php

namespace Sade\Component;

use Hashids\Hashids;
use IvoPetkov\HTML5DOMDocument;
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
     * @param  string $contents
     *
     * @return array
     */
    protected function attributes($key, $contents)
    {
        $key = strtolower($key);
        $reg = '/<\s*' . $key . '([^>]*)>(?:(.*?)<\s*\/\s*' . $key .'>|)/';
        $data = [];

        if (!preg_match_all($reg, $contents, $matches)) {
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
    protected function components($templates)
    {
        $options = $this->options;

        if (empty($options->components)) {
            return [];
        }

        if (! is_array($options->components)) {
            return [];
        }

        $components = [];
        $templateClass = $this->sade->get('template.class');

        foreach ($templates as $template) {
            foreach ($options->components as $key => $file) {
                if (is_numeric($key)) {
                    $key = pathinfo($file, PATHINFO_FILENAME);
                }

                $reg = '/<\s*' . $key . '[^>]*>(?:(.*?)<\s*\/\s*' . $key .'>|)/is';
                if (! preg_match_all($reg, $template->innerHTML, $matches)) {
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
                    $children = $this->sade->make('template.class', [
                        [
                            'component' => $options,
                            'content'   => $children,
                        ],
                        $this->sade
                    ])->render();

                    // Append children value to next component data.
                    $nextData['children'] = $children;

                    // Render child component.
                    $components[$reg] = $this->render($file, [
                        'data' => $nextData,
                    ]);
                }
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

        $mixins = $this->sade->get('mixins', []);
        $mixins = is_array($mixins) ? $mixins : [];
        $extra = array_replace_recursive($extra, $mixins);

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
        $elements = [];

        foreach ($this->sade->tags() as $tag) {
            $elements[$tag] = [];
        }

        $dom = new HTML5DOMDocument;
        $dom->loadHTML($contents);

        // Find template, script and style tags.
        foreach ($this->sade->tags() as $tag) {
            $matches = $dom->querySelectorAll($tag);
            foreach ($matches as $match) {
                $attributes = $match->getAttributes();

                if (isset($attributes['src']) && !isset($attributes['external'])) {
                    $src = $attributes['src'];
                    $match->removeAttribute('src');

                    if (file_exists($this->file($src))) {
                        $match->innerHTML = file_get_contents($this->file($src));
                    }
                }

                if (isset($attributes['external'])) {
                    $match->removeAttribute('external');
                }

                $elements[$match->nodeName][] = $match;
            }
        }

        return $elements;
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

        // Find elements.
        $elements = $this->parseFileContent(file_get_contents($filepath));

        // Call created function right before component will be created.
        if ($newData = call_user_func($this->options->get('created'))) {
            $this->options->set('data', $newData);
        }

        $output = [];
        $templates = [];

        // Determine if we should scope output.
        $scoped = $this->sade->get('scoped', false) || $this->options->scoped;

        // Render template, script and style tags.
        foreach ($this->sade->tags() as $tag) {
            // Bail if tag shouldn't be rendered.
            $enabled = $this->sade->get(sprintf('%s.enabled', $tag), true);
            if (!$enabled) {
                continue;
            }

            foreach ($elements[$tag] as $element) {
                $element->removeAttribute('scoped');

                if ($tag === 'template') {
                    $templates[] = $element;
                }

                // Call tag render method.
                $func = [$this, 'render' . ucfirst($tag)];
                $res = call_user_func_array($func, [$className, $element, $scoped]);

                if (!isset($output[$tag])) {
                    $output[$tag] = '';
                }

                $output[$tag] .= trim($res);
            }
        }

        $components = $this->components($templates);

        // Append all components in the right order.
        foreach ($components as $reg => $component) {
            foreach ($this->sade->tags() as $tag) {
                if (!isset($output[$tag], $component[$tag])) {
                    continue;
                }

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
     * @param  array  $elements
     * @param  bool   $scoped
     *
     * @return string
     */
    protected function renderTemplate($className, $element, $scoped)
    {
        return $this->sade->make('template.class', [
            [
                'attributes' => $element->getAttributes(),
                'component'  => $this->options,
                'content'    => $element->innerHTML,
                'class'      => $className,
                'file'       => basename($this->file),
                'scoped'     => $scoped,
            ],
            $this->sade
        ])->render();
    }

    /**
     * Render script tag.
     *
     * @param  string $className
     * @param  array  $elements
     * @param  bool   $scoped
     *
     * @return string
     */
    protected function renderScript($className, $element, $scoped)
    {
        return $this->sade->make('script.class', [
            [
                'attributes' => $element->getAttributes(),
                'component'  => $this->options,
                'content'    => $element->innerHTML,
                'class'      => $className,
                'scoped'     => $scoped,
            ],
            $this->sade
        ])->render();
    }

    /**
     * Render style tag.
     *
     * @param  string $className
     * @param  array  $elements
     * @param  bool   $scoped
     *
     * @return string
     */
    protected function renderStyle($className, $element, $scoped)
    {
        return $this->sade->make('style.class', [
            [
                'attributes' => $element->getAttributes(),
                'component'  => $this->options,
                'content'    => $element->innerHTML,
                'class'      => $className,
                'scoped'     => $scoped,
                'tag'        => $this->sade->get('style.tag', 'script'),
            ],
            $this->sade
        ])->render();
    }
}
