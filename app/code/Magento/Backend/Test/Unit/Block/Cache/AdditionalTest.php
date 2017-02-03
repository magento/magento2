<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Block\Cache;

class AdditionalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Cache\Additional
     */
    private $additonalBlock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\App\State | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $appStateMock;

    protected function setUp()
    {
        $this->urlBuilderMock = $this->getMock('Magento\Framework\UrlInterface');
        $this->appStateMock = $this->getMockBuilder('Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $context = $objectHelper->getObject(
            'Magento\Backend\Block\Template\Context',
            [
                'urlBuilder' => $this->urlBuilderMock,
                'appState' => $this->appStateMock,
            ]
        );

        $this->additonalBlock = $objectHelper->getObject(
            'Magento\Backend\Block\Cache\Additional',
            ['context' => $context]
        );
    }

    public function testGetCleanImagesUrl()
    {
        $expectedUrl = 'cleanImagesUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanImages')
            ->will($this->returnValue($expectedUrl));
        $this->assertEquals($expectedUrl, $this->additonalBlock->getCleanImagesUrl());
    }

    public function testGetCleanMediaUrl()
    {
        $expectedUrl = 'cleanMediaUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanMedia')
            ->will($this->returnValue($expectedUrl));
        $this->assertEquals($expectedUrl, $this->additonalBlock->getCleanMediaUrl());
    }

    public function testGetCleanStaticFiles()
    {
        $expectedUrl = 'cleanStaticFilesUrl';
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/cleanStaticFiles')
            ->will($this->returnValue($expectedUrl));
        $this->assertEquals($expectedUrl, $this->additonalBlock->getCleanStaticFilesUrl());
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
        $this->assertEquals($expected, $this->additonalBlock->isInProductionMode());
    }

    public function isInProductionModeDataProvider()
    {
        return [
            [\Magento\Framework\App\State::MODE_DEFAULT, false],
            [\Magento\Framework\App\State::MODE_DEVELOPER, false],
            [\Magento\Framework\App\State::MODE_PRODUCTION, true],
        ];
    }
}
