<?php

use Frozzare\Rain\Rain;
use PHPUnit\Framework\TestCase;

class RainTest extends TestCase
{
    public function setUp()
    {
        $this->rain = new Rain([
            'src_dir' => __DIR__ . '/testdata'
        ]);
    }

    public function testRender()
    {
        $output = $this->rain->render('hello.php');
        $output = trim($output);

        $this->assertSame('<p>Hello, world!</p>', $output);
    }
}