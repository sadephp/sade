<?php

namespace Frozzare\Rain;

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
        'id'         => '',
        'methods'    => [],
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

        $loader = new Twig_Loader_Array([
            'component.rain' => $this->options['content'],
        ]);

        $this->twig = new Twig_Environment($loader);

        foreach ($this->options['methods'] as $key => $value) {
            if (!is_callable($value)) {
                continue;
            }

            $this->twig->addFunction(new Twig_Function($key, $value));
        }
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
        $html = $this->twig->render('component.rain', $data);

        if ($this->options['scoped']) {
            $html = sprintf('<div id="%s">%s</div>', $this->options['id'], $html);
        }

        return $html;
    }
}
