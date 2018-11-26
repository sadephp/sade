<?php

namespace Sade\Component;

use Twig_Environment;
use Twig_Filter;
use Twig_Function;
use Twig_Loader_Array;

class Template extends Tag
{
    /**
     * Template options.
     *
     * @var array
     */
    protected $options = [
        'attributes' => [],
        'component'  => null,
        'content'    => '',
        'class'      => '',
        'file'       => 'component.sade',
        'scoped'     => false,
    ];

    /**
     * Twig environment.
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Register filters.
     */
    protected function registerFilters()
    {
        if (empty($this->options['component'])) {
            return;
        }

        foreach ($this->options['component']->get('filters') as $key => $value) {
            if (!is_callable($value)) {
                continue;
            }

            $this->twig->addFilter(new Twig_Filter($key, $value));
        }
    }

    /**
     * Register methods.
     */
    protected function registerMethods()
    {
        if (empty($this->options['component'])) {
            return;
        }

        foreach ($this->options['component']->get('methods') as $key => $value) {
            if (!is_callable($value)) {
                continue;
            }

            $this->twig->addFunction(new Twig_Function($key, $value));
        }
    }

    /**
     * Render template.
     *
     * @return string
     */
    public function render()
    {
        $this->setupTwig();
        $this->registerFilters();
        $this->registerMethods();

        $data = empty($this->options['component']) ? [] : $this->options['component']->get('data');
        $data['sade_classname'] = $this->options['class'];

        $html = $this->twig->render($this->options['file'], $data);

        if (!$this->options['scoped']) {
            return $html;
        }

        $attributes = $this->options['attributes'];

        if (!is_array($attributes)) {
            $attributes = [];
        }

        if (empty($attributes['class'])) {
            $attributes['class'] = $this->options['class'];
        } else {
            $attributes['class'] .= ' ' . $this->options['class'];
        }

        $attr_html = '';

        foreach ($attributes as $key => $value) {
            if (empty($value)) {
                $attr_html .= sprintf('%s ', $key);
            } else {
                $attr_html .= sprintf('%s="%s" ', $key, $value);
            }
        }

        return sprintf('<div %s>%s</div>', $attr_html, $html);
    }

    /**
     * Setup twig.
     */
    protected function setupTwig()
    {
        $options = [];
        $options[$this->options['file']] = $this->options['content'];

        $loader = new Twig_Loader_Array($options);
        $this->twig = new Twig_Environment($loader);
    }
}
