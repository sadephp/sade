<?php

namespace Frozzare\Rain;

use Twig_Loader_Array;
use Twig_Environment;

class Template
{

    /**
     * Template options.
     *
     * @var array
     */
    protected $options;

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
    public function __construct($options)
    {
        $this->options = $options;

        $loader = new Twig_Loader_Array([
            'file.rain' => $options['content']
        ]);

        $this->twig = new Twig_Environment($loader);
    }

    /**
     * Render template.
     *
     * @param  array $data
     *
     * @return string
     */
    public function render($data = [])
    {
        $html = $this->twig->render('file.rain', $data);

        if ($this->options['scoped']) {
            $html = sprintf('<div id="%s">%s</div>', $this->options['id'], $html);
        }

        return $html;
    }
}
