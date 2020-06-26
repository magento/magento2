<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Cache;

use Magento\Backend\Block\Cache\Additional;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdditionalTest extends TestCase
{
    /**
     * @var Additional
     */
    private $additionalBlock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var State|MockObject
     */
    protected $appStateMock;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->appStateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new ObjectManager($this);
        $context = $objectHelper->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'appState' => $this->appStateMock,
            ]
        );

        $this->additionalBlock = $objectHelper->getObject(
            Additional::class,
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
            [State::MODE_DEFAULT, false],
            [State::MODE_DEVELOPER, false],
            [State::MODE_PRODUCTION, true],
        ];
    }
}
