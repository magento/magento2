<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code\Test\Unit\Minifier\Adapter\Css;

class CssMinTest extends \PHPUnit_Framework_TestCase
{
    public function testMinify()
    {
        $cssMinMock = $this->getMockBuilder(\CSSmin::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cssMinAdapter = new \Magento\Framework\Code\Minifier\Adapter\Css\CSSmin($cssMinMock);
        $property = new \ReflectionProperty(\Magento\Framework\Code\Minifier\Adapter\Css\CSSmin::class, 'cssMinifier');
        $property->setAccessible(true);
        $property->setValue($cssMinAdapter, $cssMinMock);

        $expectedResult = 'minified content';
        $cssMinMock->expects($this->once())->method('run')->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $cssMinAdapter->minify('not minified'));
    }
}
