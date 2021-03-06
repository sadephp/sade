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

    public function tearDown()
    {
        unset($this->sade);
    }

    public function testCustomConfig()
    {
        $this->assertTrue($this->sade->get('custom'));
    }

    public function testMixinsRender()
    {
        $this->assertNotEmpty($this->sade->get('mixins'));

        $output = $this->sade->render('mixins.php');

        $this->assertContains('<p>Hello, world!</p>', $output);
        $this->assertContains('<p>created1</p>', $output);
        $this->assertContains('<p>created2</p>', $output);
        $this->assertContains('<p>method1</p>', $output);
        $this->assertContains('<p>method2</p>', $output);
        $this->assertContains('<p>filter1</p>', $output);
        $this->assertContains('<p>filter2</p>', $output);
    }

    public function testChildrenRender()
    {
        $output = $this->sade->render('children');

        $this->assertSame('<p>Hello, world!</p>', $output);
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

    public function testCreatedRender()
    {
        $output = $this->sade->render('created.php');

        $this->assertSame('<p>Created, world!</p>', $output);
    }

    public function testCustomTemplateTagClass()
    {
        require __DIR__ . '/testdata/CustomTemplateTag.php';

        $sade = new Sade(__DIR__ . '/testdata', [
            'cache'    => false,
            'template' => [
                'class' => 'CustomTemplateTag',
            ]
        ]);

        $output = $sade->render('hello.php');

        $this->assertSame('CustomTemplateTag', $output);
    }

    public function testEnvRender()
    {
        $output = $this->sade->render('env.php');

        $this->assertContains('Hello, world', $output);
    }

    public function testExternalSourceRender()
    {
        $output = $this->sade->render('accordion/external.php');

        $this->assertContains('src="accordion.js"', $output);
        $this->assertContains('var tag = "link"', $output);
        $this->assertContains('elm.setAttribute(\'href\'', $output);
    }

    public function testMultipleFilesRender()
    {
        $output = $this->sade->render('accordion/accordion.php');

        $this->assertTrue(strpos($output, '<div') !== false && strpos($output, '<script') !== false && strpos($output, 'tag = "style"') !== false);
        $this->assertRegExp('/\<div\sclass\=\"sade\-\w+\"/', $output);
    }

    public function testScriptAndStyleTagsCountRender()
    {
        $sade = new Sade(__DIR__ . '/testdata');
        $output = $sade->render('accordion/accordion.php');
        $output .= $sade->render('accordion/accordion.php');

        // Style script only once.
        $this->assertSame(1, substr_count($output, 'document.createElement'));

        // Regular script + style script.
        $this->assertSame(2, substr_count($output, '<script'));
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

    public function testPluginRender()
    {
        $output = $this->sade->render('ip.php');

        $this->assertRegExp('/1\.1\.1\.1/', $output);
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

    public function testWithFunctionRender()
    {
        $output = $this->sade->render('app/app.php');

        $this->assertContains('Provider prop: App Name', $output);
    }
}
