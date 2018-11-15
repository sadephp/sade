<?php

namespace Sade;

use Twig_Environment;
use Twig_Function;
use Twig_Loader_Array;

class Template
{
    /**
     * Template options.
     *
     * @var array
     */
    protected $options = [
        'attributes' => [],
        'content'    => '',
        'data'       => null,
        'id'         => '',
        'scoped'     => false,
    ];

    /**
     * Twig environment.
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Template construct.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        $this->setupTwig();
        $this->registerFilters();
        $this->registerMethods();
    }

    /**
     * Register filters.
     */
    protected function registerFilters()
    {
        if (empty($this->options['data'])) {
            return;
        }

        foreach ($this->options['data']->get('filters') as $key => $value) {
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
        if (empty($this->options['data'])) {
            return;
        }

        foreach ($this->options['data']->get('methods') as $key => $value) {
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
        $data = empty($this->options['data']) ? [] : $this->options['data']->get('data');
        $html = $this->twig->render('component.sade', $data);

        if (!$this->options['scoped']) {
            return $html;
        }

        $attributes = $this->options['attributes'];

        if (!is_array($attributes)) {
            $attributes = [];
        }

        if (empty($attributes['class'])) {
            $attributes['class'] = $this->options['id'];
        } else {
            $attributes['class'] .= ' ' . $this->options['id'];
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
        $loader = new Twig_Loader_Array([
            'component.sade' => $this->options['content'],
        ]);

        $this->twig = new Twig_Environment($loader);
    }
}
