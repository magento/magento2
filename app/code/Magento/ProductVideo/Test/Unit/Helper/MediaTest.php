<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Helper;

use Magento\ProductVideo\Helper\Media;

class MediaTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $scopeConfigMock;

    /**
     * @var \Magento\ProductVideo\Helper\Media|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * Create mock objects
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->contextMock = $this->createMock(\Magento\Framework\App\Helper\Context::class);
        $this->contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->helper = new \Magento\ProductVideo\Helper\Media(
            $this->contextMock
        );
    }

    /**
     * Test for method getPlayIfBaseAttribute
     */
    public function testGetPlayIfBaseAttribute()
    {
        $return = 'some_value';
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with(Media::XML_PATH_PLAY_IF_BASE)
            ->willReturn($return);

        $this->assertEquals(
            $return,
            $this->helper->getPlayIfBaseAttribute()
        );
    }

    /**
     * Test for method getShowRelatedAttribute
     */
    public function testGetShowRelatedAttribute()
    {
        $return = 'some_value';
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with(Media::XML_PATH_SHOW_RELATED)
            ->willReturn($return);

        $this->assertEquals(
            $return,
            $this->helper->getShowRelatedAttribute()
        );
    }

    /**
     * Test for method getVideoAutoRestartAttribute
     */
    public function testGetVideoAutoRestartAttribute()
    {
        $return = 'some_value';
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with(Media::XML_PATH_VIDEO_AUTO_RESTART)
            ->willReturn($return);

        $this->assertEquals(
            $return,
            $this->helper->getVideoAutoRestartAttribute()
        );
    }

    /**
     * Test for method getYouTubeApiKey
     */
    public function testGetYouTubeApiKey()
    {
        $return = 'some_value';
        $this->scopeConfigMock->expects($this->once())->method('getValue')
            ->with(Media::XML_PATH_YOUTUBE_API_KEY)
            ->willReturn($return);

        $this->assertEquals(
            $return,
            $this->helper->getYouTubeApiKey()
        );
    }
}
