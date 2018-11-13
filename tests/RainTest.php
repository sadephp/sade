<?php

use Frozzare\Rain\Rain;
use PHPUnit\Framework\TestCase;

class RainTest extends TestCase
{
    public function setUp()
    {
        $this->rain = new Rain(__DIR__ . '/testdata');
    }

    public function testRender()
    {
        $output = $this->rain->render('hello.php');

        $this->assertSame('<p>Hello, world!</p>', $output);

        $output = $this->rain->render('hello.php', [
            'data' => [
                'greeting' => 'Rain',
            ],
        ]);

        $this->assertSame('<p>Rain, world!</p>', $output);
    }
}