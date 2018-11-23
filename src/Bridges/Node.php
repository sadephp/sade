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
     * Node constructor.
     *
     * @param string $dir
     * @param string $file
     */
    public function __construct($dir, $file = 'sade.js')
    {
        $this->dir = $dir;
        $this->file = $file;
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
        $path = $this->dir . '/' . $this->file;

        if (!file_exists($path)) {
            return $code;
        }

        $process = proc_open('node ' . $path, [
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
