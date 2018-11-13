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

    public function testComponentsRender()
    {
        $output = $this->rain->render('components.php');

        $this->assertSame('<span><p>Hello, world!</p></span>', $output);

        $output = $this->rain->render('components.php', [
            'data' => [
                'greeting' => 'Rain',
            ],
        ]);

        $this->assertSame('<span><p>Rain, world!</p></span>', $output);
    }

    public function testMultipleFilesRender()
    {
        $output = $this->rain->render('accordion/accordion.php');

        $this->assertTrue(strpos($output, '<div') !== false && strpos($output, '<script') !== false && strpos($output, '<style') !== false);
    }
}