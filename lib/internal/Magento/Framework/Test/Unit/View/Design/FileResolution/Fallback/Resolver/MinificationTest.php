<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\View\Design\FileResolution\Fallback\Resolver\Minification;

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
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolverMock;

    /**
     * @var \Magento\Framework\View\Asset\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->resolverMock = $this->getMockBuilder('Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder('Magento\Framework\View\Asset\ConfigInterface')
            ->setMethods(['isAssetMinification'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->minification = new Minification(
            $this->resolverMock,
            $this->configMock
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
    public function testResolve($isEnabled, $requested, $alternative, $expected, $resolvedOriginal, $resolvedAlternative)
    {
        $this->configMock
            ->expects($this->any())
            ->method('isAssetMinification')
            ->willReturnMap([['css', $isEnabled]]);

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
            [false, 'file.min.css', 'file.css', false, false, 'found.css']
        ];
    }
}
