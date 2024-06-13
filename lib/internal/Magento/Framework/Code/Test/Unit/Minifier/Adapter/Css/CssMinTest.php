<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Test\Unit\Minifier\Adapter\Css;

use PHPUnit\Framework\TestCase;
use tubalmartin\CssMin\Minifier;
use Magento\Framework\Code\Minifier\Adapter\Css\CSSmin;

class CssMinTest extends TestCase
{
    public function testMinify()
    {
        $cssMinMock = $this->getMockBuilder(Minifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cssMinAdapter = new CSSmin($cssMinMock);
        $property = new \ReflectionProperty(CSSmin::class, 'cssMinifier');
        $property->setAccessible(true);
        $property->setValue($cssMinAdapter, $cssMinMock);

        $expectedResult = 'minified content';
        $cssMinMock->expects($this->once())->method('run')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $cssMinAdapter->minify('not minified'));
    }
}
