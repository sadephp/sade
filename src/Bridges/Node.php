<?php

namespace Sade\Bridges;

class Node
{
    /**
     * Node file directory.
     *
     * @var string
     */
    protected $dir;

    /**
     * Node file.
     *
     * @var string
     */
    protected $file = '';

    /**
     * Path to node binary.
     *
     * @var string
     */
    protected $path;

    /**
     * Node constructor.
     *
     * @param string $dir
     * @param array  $options
     */
    public function __construct($dir, array $options = [])
    {
        $this->dir = $dir;

        $options = array_merge($options, [
            'file' => 'node.js',
            'path' => 'node',
        ]);

        $this->file = $options['file'];
        $this->path = $options['path'];

    }

    /**
     * Run node process and send data.
     *
     * @param  string $code
     * @param  string $type
     *
     * @return string
     */
    public function run($code, $type)
    {
        $file = $this->dir . '/' . $this->file;

        if (!file_exists($file)) {
            return $code;
        }

        $process = proc_open(sprintf('%s %s', $this->path, $file), [
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ], $pipes);

        // Send data to stdin.
        fwrite($pipes[0], json_encode([
            'code' => $code,
            'type' => $type,
        ]));
        fclose($pipes[0]);

        // Read the outputs
        $contents = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        $return_value = proc_close($process);

        if (!empty($errors)) {
            return $errors;
        }

        return $contents;
    }
}
