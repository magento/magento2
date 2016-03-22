<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Minification;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;

/**
 * Unit test for Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Minification
 */
class MinificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Minification
     */
    protected $minification;

    /**
     * @var ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolverMock;

    /**
     * @var \Magento\Framework\View\Asset\Minification|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $assetMinificationMock;
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->resolverMock = $this
            ->getMockBuilder('Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assetMinificationMock = $this->getMockBuilder('Magento\Framework\View\Asset\Minification')
            ->disableOriginalConstructor()
            ->getMock();

        $this->minification = new Minification(
            $this->resolverMock,
            $this->assetMinificationMock
        );
    }

    /**
     * @param bool $isEnabled
     * @param string $requested
     * @param string $alternative
     * @param string $expected
     * @param string $resolvedOriginal
     * @param string $resolvedAlternative
     * @return void
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        $isEnabled,
        $requested,
        $alternative,
        $expected,
        $resolvedOriginal,
        $resolvedAlternative
    ) {
        $this->assetMinificationMock
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturnMap([['css', $isEnabled]]);
        $this->assetMinificationMock
            ->expects($this->any())
            ->method('removeMinifiedSign')
            ->with($requested)
            ->willReturn($alternative);

        $this->resolverMock
            ->expects($this->any())
            ->method('resolve')
            ->withConsecutive(
                ['', $requested, null, null, null, null],
                ['', $alternative, null, null, null, null]
            )
            ->willReturnOnConsecutiveCalls($resolvedOriginal, $resolvedAlternative);

        $this->assertEquals($expected, $this->minification->resolve('', $requested));
    }

    /**
     * @return array
     */
    public function resolveDataProvider()
    {
        return [
            [true, 'file.min.css', 'file.css', 'found.css', false, 'found.css'],
            [false, 'file.min.css', 'file.min.css', false, false, 'found.css']
        ];
    }
}
