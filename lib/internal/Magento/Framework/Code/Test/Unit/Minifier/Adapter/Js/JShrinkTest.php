<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Minifier\Adapter\Js;

use PHPUnit\Framework\TestCase;
use Magento\Framework\Code\Minifier\Adapter\Js\JShrink;

class JShrinkTest extends TestCase
{
    public function testMinify()
    {
        $content = file_get_contents(__DIR__ . '/../../_files/js/original.js');
        $minifier = new JShrink();
        $actual = $minifier->minify($content);
        $expected = "var one='one';var two='two';";
        $this->assertEquals($expected, $actual);
    }
}
