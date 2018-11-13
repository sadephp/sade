<?php

namespace Sade;

class Cache
{
    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Cache construct.
     *
     * @param array $options
     */
    public function __construct($options)
    {
        $defaults = [
            'dir'  => '',
            'perm' => ( 0755 & ~ umask() ),
        ];

        $options = is_array($options) ? $options : [];
        $this->options = array_merge($defaults, $options);
    }

    /**
     * Get cache file with directory.
     *
     * @param  string $file
     *
     * @return string
     */
    protected function file($file)
    {
        $dir = rtrim($this->options['dir'], '/') . '/';
        return $dir . $file;
    }

    /**
     * Get cached content for file.
     *
     * @param  string $file
     * @param  int    $time
     *
     * @return string
     */
    public function get($file, $time = null)
    {
        if (empty($this->options['dir'])) {
            return;
        }

        $file = $this->file($file);

        if (file_exists($file)) {
            if (!empty($time)) {
                if ($time > filemtime($file)) {
                    unlink($file);
                    return null;
                }
            }

            return @file_get_contents($file);
        }
    }

    /**
     * Set cached contnet for file.
     *
     * @param  string $file
     * @param  string $content
     */
    public function set($file, $content)
    {
        if (empty($this->options['dir'])) {
            return;
        }

        if (!is_dir($this->options['dir'])) {
            @mkdir($this->options['dir'], $this->options['perm']);
        }

        $file = $this->file($file);

        file_put_contents($file, $content);
    }
}
