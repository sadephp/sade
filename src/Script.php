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
        'scoped'     => false,
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

        if ($this->options['scoped'] && empty($attributes['data-sade-class']) && !empty($this->options['id'])) {
            $attributes['data-sade-class'] = $this->options['id'];
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

        if (!defined('SADE_DEV')) {
            $content = preg_replace('/\s+/', ' ', trim($content));
        }

        return sprintf('<script %s>%s</script>', $attr_html, $content);
    }
}
