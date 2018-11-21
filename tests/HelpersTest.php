<?php

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testEnv()
    {
        $tests = [
            'true' => true,
            'false' => false,
            '"hello"' => 'hello',
            'null' => null,
        ];

        foreach ($tests as $value => $expected) {
            putenv(sprintf('%s=%s', $value, $value));
            $this->assertSame($expected, env($value));
        }
    }

    public function testValue()
    {
        $tests = [
            1 => 1,
            'hello' => 'hello',
            'hello' => function () {
                return 'hello';
            },
        ];

        foreach ($tests as $expected => $value) {
            $this->assertSame($expected, value($value));
        }
    }
}
