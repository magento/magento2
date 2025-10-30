<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Test\Unit\Minifier\Adapter\Js;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\Minifier\Adapter\Js\JShrink;

class JShrinkTest extends TestCase
{
    /**
     * @param string $content
     * @param string $expected
     * @return void
     * @throws \Exception
     * @dataProvider minifyDataProvider
     */
    public function testMinify(string $content, string $expected): void
    {
        $minifier = new JShrink();
        $actual = $minifier->minify($content);
        $this->assertEquals($expected, $actual);
    }

    public static function minifyDataProvider(): array
    {
        return [
            'line breaks' => [
                'content' => file_get_contents(__DIR__ . '/../../_files/js/original.js'),
                'expected' => "var one='one';var two='two';"
            ],
            'regex1' => [
                'content' => <<<JS
function test (string) {
  return (string || '').replace(
    /([\\!"#$%&'()*+,./:;<=>?@\[\]^`{|}~])/g,
    '\\$1'
  )
}
JS,
                'expected' => <<<JS
function test(string){return(string||'').replace(/([\\!"#$%&'()*+,./:;<=>?@\[\]^`{|}~])/g,'\\$1')}
JS
            ],
            'regex2' => [
                'content' => <<<JS
function test(str) {
    return (/^[a-zA-Z\d\-_/:.[\]&@()! ]+$/i).test(str);
}
JS,
                'expected' => <<<JS
function test(str){return(/^[a-zA-Z\d\-_/:.[\]&@()! ]+$/i).test(str);}
JS
            ],
        ];
    }
}
