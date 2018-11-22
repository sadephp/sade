<?php

namespace Sade\Component;

class Script extends Tag
{
    /**
     * Script options.
     *
     * @var array
     */
    protected $options = [
        'attributes' => [],
        'component'  => null,
        'content'    => '',
        'class'      => '',
        'scoped'     => false,
    ];

    /**
     * Render script html.
     *
     * @return string
     */
    public function render()
    {
        $attributes = $this->options['attributes'];

        if (!is_array($attributes)) {
            $attributes = [];
        }

        if (empty($attributes['type'])) {
            $attributes['type'] = 'text/javascript';
        }

        if ($this->options['scoped'] && empty($attributes['data-sade-class']) && !empty($this->options['class'])) {
            $attributes['data-sade-class'] = $this->options['class'];
        }

        $attr_html = '';

        foreach ($attributes as $key => $value) {
            if (empty($value)) {
                $attr_html .= sprintf('%s ', $key);
            } else {
                $attr_html .= sprintf('%s="%s" ', $key, $value);
            }
        }

        $content = $this->options['content'];

        if (empty($content) && !isset($attributes['src'])) {
            return '';
        }

        if (!isset($attributes['src'])) {
            $class = $this->sade->option('template.class');
            $content = (new $class([
                'component' => $this->options['component'],
                'content'   => $content,
                'class'     => $this->options['class'],
            ], $this->sade))->render();
        }

        $node = $this->sade->get('sade.bridges.node');
        $content = $node->run($content, 'script');

        return sprintf('<script %s>%s</script>', $attr_html, $content);
    }
}
