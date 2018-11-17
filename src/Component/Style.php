<?php

namespace Sade\Component;

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
        'component'  => null,
        'content'    => '',
        'class'      => '',
        'scoped'     => false,
        'tag'        => 'script',
    ];

    /**
     * Style construct.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        if (isset($this->options['attributes']['src'])) {
            $this->options['tag'] = 'script';
        }
    }

    /**
     * Get attributes.
     *
     * @return array
     */
    protected function attributes()
    {
        $attributes = $this->options['attributes'];

        if (!is_array($attributes)) {
            $attributes = [];
        }

        if (isset($attributes['src'])) {
            if (empty($attributes['rel'])) {
                $attributes['rel'] = 'stylesheet';
            }
        }

        if (empty($attributes['type'])) {
            $attributes['type'] = 'text/css';
        }

        if ($this->options['scoped'] && empty($attributes['data-sade-class']) && !empty($this->options['class'])) {
            $attributes['data-sade-class'] = $this->options['class'];
        }

        if (isset($attributes['scoped'])) {
            unset($attributes['scoped']);
        }

        return $attributes;
    }

    /**
     * Get style content.
     *
     * @return string
     */
    protected function content()
    {
        if (empty($this->options['content'])) {
            return '';
        }

        $content = $this->options['content'];
        $content = (new Template([
            'component' => $this->options['component'],
            'content'   => $content,
            'class'     => $this->options['class'],
        ]))->render();

        $css = (new Parser($content))->parse();

        if ($this->options['scoped']) {
            foreach ($css->getAllDeclarationBlocks() as $block) {
                foreach ($block->getSelectors() as $selector) {
                    $selector->setSelector('.' . $this->options['class'] . ' ' . $selector->getSelector());
                }
            }
        }

        return preg_replace('/\s+/', ' ', trim($css->render()));
    }

    /**
     * Render style html.
     *
     * @return string
     */
    public function render()
    {
        if ($this->options['tag'] === 'script') {
            return $this->renderScript();
        }

        $content = $this->content();

        if (empty($content)) {
            return '';
        }

        $attributes = $this->attributes();
        $attr_html = '';

        foreach ($attributes as $key => $value) {
            if (empty($value)) {
                $attr_html .= sprintf('%s ', $key);
            } else {
                $attr_html .= sprintf('%s="%s" ', $key, $value);
            }
        }

        return sprintf('<style %s>%s</style>', $attr_html, $content);
    }

    /**
     * Render CSS as a script tag.
     *
     * @return string
     */
    protected function renderScript()
    {
        $content = $this->content();

        if (empty($content) && !isset($this->options['attributes']['src'])) {
            return '';
        }

        $tag = isset($this->options['attributes']['src']) ? 'link' : 'style';
        $attributes = $this->attributes();
        $attr_script = '';

        foreach ($attributes as $key => $value) {
            $attr_script .= sprintf("elm.setAttribute('%s', '%s');\n", $key, $value);
        }

        $script = '
        (function() {
            var tag = "%s";
            var elm = document.createElement(tag);

            %s

            if (tag === "style") {
                var styles = "%s";

                if (elm.styleSheet) {
                    elm.styleSheet.cssText = styles;
                } else {
                    elm.appendChild(document.createTextNode(styles));
                }
            }

            var s = document.getElementsByTagName(tag);
            if (s.length) {
                s[0].parentNode.appendChild(elm);
            } else {
                var head = document.getElementsByTagName("head");
                head.length && head[0].appendChild(elm);
            }
        }());
        ';

        $content = str_replace('"', '\"', $content);
        $content = sprintf($script, $tag, $attr_script, $content);

        return (new Script([
            'content' => $content,
        ]))->render();
    }
}
