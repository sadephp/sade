<?php

namespace Sade;

use Sabberworm\CSS\Parser;

class Style
{
    /**
     * Style options.
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
     * Style construct.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Render style html.
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
            $attributes['type'] = 'text/css';
        }

        if (isset($attributes['scoped'])) {
            unset($attributes['scoped']);
        }

        $attr_html = '';

        foreach ($attributes as $key => $value) {
            if (empty($value)) {
                $attr_html .= sprintf('%s ', $key);
            } else {
                $attr_html .= sprintf('%s="%s" ', $key, $value);
            }
        }

        $css = (new Parser($this->options['content']))->parse();

        if ($this->options['scoped']) {
            foreach ($css->getAllDeclarationBlocks() as $block) {
                foreach ($block->getSelectors() as $selector) {
                    $selector->setSelector('#' . $this->options['id'] . ' ' . $selector->getSelector());
                }
            }
        }

        $content = $css->render();

        if (empty($content) && !isset($attributes['src'])) {
            return '';
        }

        return sprintf('<style %s>%s</style>', $attr_html, $content);
    }
}
