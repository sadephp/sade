<?php

use Sade\Sade;
use PHPUnit\Framework\TestCase;

class SadeTest extends TestCase
{
    public function setUp()
    {
        $this->sade = new Sade(__DIR__ . '/testdata', [
            'cache' => false,
        ]);
    }

    public function testClassName()
    {
        $output = $this->sade->className('components.php');

        $this->assertRegExp('/sade\-\w+/', $output);
    }

    public function testComponentsRender()
    {
        $output = $this->sade->render('components.php');

        $this->assertSame('<span><p>Hello, world!</p></span>', $output);

        $output = $this->sade->render('components.php', [
            'data' => [
                'greeting' => 'Sade',
            ],
        ]);

        $this->assertSame('<span><p>Sade, world!</p></span>', $output);
    }

    public function testMultipleFilesRender()
    {
        $output = $this->sade->render('accordion/accordion.php');

        $this->assertTrue(strpos($output, '<div') !== false && strpos($output, '<script') !== false && strpos($output, 'tag = "style"') !== false);
        $this->assertRegExp('/\<div\sclass\=\"sade\-\w+\"/', $output);
    }

    public function testMethodsRender()
    {
        $output = $this->sade->render('methods.php');

        $this->assertSame('<p>Hello, world!</p>', $output);

        $output = $this->sade->render('methods.php', [
            'data' => [
                'greeting' => 'Methods',
            ],
        ]);

        $this->assertSame('<p>Methods, world!</p>', $output);
    }

    public function testOnlyRender()
    {
        $output = $this->sade->only('script')->render('accordion/accordion.php');

        $this->assertTrue(strpos($output, '<div') === false && strpos($output, '<script') !== false && strpos($output, 'tag = "style"') === false);
    }

    public function testRender()
    {
        $output = $this->sade->render('hello.php');

        $this->assertSame('<p>Hello, world!</p>', $output);

        $output = $this->sade->render('hello.php', [
            'data' => [
                'greeting' => 'Sade',
            ],
        ]);

        $this->assertSame('<p>Sade, world!</p>', $output);
    }
}