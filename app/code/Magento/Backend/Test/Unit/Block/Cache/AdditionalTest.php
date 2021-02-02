<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Cache;

class AdditionalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Backend\Block\Cache\Additional
     */
    private $additionalBlock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\App\State | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $appStateMock;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            \Magento\Backend\Block\Template\Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'appState' => $this->appStateMock,
            ]
        );

        $this->additionalBlock = $objectHelper->getObject(
            \Magento\Backend\Block\Cache\Additional::class,
            ['context' => $context]
        );
    }

    public function testGetCleanImagesUrl()
    {
        $expectedUrl = 'cleanImagesUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanImages')
            ->willReturn($expectedUrl);
        $this->assertEquals($expectedUrl, $this->additionalBlock->getCleanImagesUrl());
    }

    public function testGetCleanMediaUrl()
    {
        $expectedUrl = 'cleanMediaUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanMedia')
            ->willReturn($expectedUrl);
        $this->assertEquals($expectedUrl, $this->additionalBlock->getCleanMediaUrl());
    }

    public function testGetCleanStaticFiles()
    {
        $expectedUrl = 'cleanStaticFilesUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanStaticFiles')
            ->willReturn($expectedUrl);
        $this->assertEquals($expectedUrl, $this->additionalBlock->getCleanStaticFilesUrl());
    }

    /**
     * @param string $mode
     * @param bool $expected
     * @dataProvider isInProductionModeDataProvider
     */
    public function testIsInProductionMode($mode, $expected)
    {
        $this->appStateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->assertEquals($expected, $this->additionalBlock->isInProductionMode());
    }

    /**
     * @return array
     */
    public function isInProductionModeDataProvider()
    {
        return [
            [\Magento\Framework\App\State::MODE_DEFAULT, false],
            [\Magento\Framework\App\State::MODE_DEVELOPER, false],
            [\Magento\Framework\App\State::MODE_PRODUCTION, true],
        ];
    }
}
