<?php

namespace Sade;

class Script
{
    /**
     * Script options.
     *
     * @var array
     */
    protected $options = [
        'attributes' => [],
        'content'    => '',
        'id'         => '',
    ];

    /**
     * Script construct.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

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

        if (empty($attributes['data-sade-id'])) {
            $attributes['data-sade-id'] = $this->options['id'];
        }

        $attr_html = '';

        foreach ($attributes as $key => $value) {
            $attr_html .= sprintf('%s="%s" ', $key, $value);
        }

        $content = $this->options['content'];

        if (empty($content)) {
            return '';
        }

        return sprintf('<script %s>%s</script>', $attr_html, $content);
    }
}
