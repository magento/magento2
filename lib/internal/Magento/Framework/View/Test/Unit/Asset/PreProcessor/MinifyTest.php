<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessor\Minify;

/**
 * Unit test for Magento\Framework\View\Asset\PreProcessor\Minify
 */
class MinifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Minify
     */
    protected $minify;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapterMock;

    /**
     * @var \Magento\Framework\View\Asset\Minification|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $minificationMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->adapterMock = $this->getMockBuilder('Magento\Framework\Code\Minifier\AdapterInterface')
            ->setMethods(['minify'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->minificationMock = $this->getMockBuilder('Magento\Framework\View\Asset\Minification')
            ->disableOriginalConstructor()
            ->getMock();

        $this->minify = new Minify(
            $this->adapterMock,
            $this->minificationMock
        );
    }

    /**
     * @param string $targetPath
     * @param string $originalPath
     * @param int $minifyCalls
     * @param int $setContentCalls
     * @param bool $isEnabled
     * @return void
     * @dataProvider processDataProvider
     */
    public function testProcess($targetPath, $originalPath, $minifyCalls, $setContentCalls, $isEnabled)
    {
        $chainMock = $this->getMockBuilder('Magento\Framework\View\Asset\PreProcessor\Chain')
            ->disableOriginalConstructor()
            ->getMock();
        $chainMock
            ->expects($this->any())
            ->method('getTargetAssetPath')
            ->willReturn($targetPath);
        $chainMock
            ->expects($this->exactly($setContentCalls))
            ->method('setContent')
            ->with('minified content');
        $chainMock
            ->expects($this->any())
            ->method('getContent')
            ->willReturn('original content');
        $chainMock
            ->expects($this->any())
            ->method('getOrigAssetPath')
            ->willReturn($originalPath);

        $this->adapterMock
            ->expects($this->exactly($minifyCalls))
            ->method('minify')
            ->with('original content')
            ->willReturn('minified content');

        $this->minificationMock
            ->expects($this->any())
            ->method('isEnabled')
            ->willReturnMap([['css', $isEnabled]]);

        $this->minificationMock
            ->expects($this->any())
            ->method('isMinifiedFilename')
            ->willReturnMap(
                [
                    ['test.min.css', true],
                    ['test.jpeg', false],
                    ['test.css', false]
                ]
            );

        $this->minify->process($chainMock);
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            ['test.min.css', 'test.css', 1, 1, true],
            ['test.min.css', 'test.min.css', 0, 0, true],
            ['test.jpeg', 'test.jpeg', 0, 0, true],
            ['test.css', 'test.css', 0, 0, true],
            ['test.jpeg', 'test.jpeg', 0, 0, true],
            ['test.css', 'test.css', 0, 0, true],
            ['test.css', 'test.css', 0, 0, false]
        ];
    }
}
