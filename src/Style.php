<?php

namespace Frozzare\Rain;

use Sabberworm\CSS\Parser;

class Style
{
    /**
     * Style options.
     *
     * @var array
     */
    protected $options = [
        'content' => '',
        'id' => '',
    ];

    /**
     * CSS Parser
     *
     * @var \Sabberworm\CSS\Parser
     */
    protected $parser;

    /**
     * Style construct.
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $this->options = array_merge($this->options, $options);
        $this->parser = new Parser($this->options['content']);
    }

    /**
     * Render style html.
     *
     * @return string
     */
    public function render()
    {
        $css = $this->parser->parse();

        foreach ($css->getAllDeclarationBlocks() as $block) {
            foreach ($block->getSelectors() as $selector) {
                $selector->setSelector('#' . $this->options['id'] . ' ' . $selector->getSelector());
            }
        }

        return '<style type="text/css">' . $css->render() . '</style>';
    }
}
