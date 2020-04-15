<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\ProductVideo\Helper\Media;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    /** @var ScopeConfigInterface|MockObject */
    protected $scopeConfigMock;

    /**
     * @var Media|MockObject
     */
    protected $helper;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * Create mock objects
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $this->contextMock = $this->createMock(Context::class);
        $this->contextMock->expects($this->any())->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->helper = new Media(
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
            ->will($this->returnValue($return));

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
            ->will($this->returnValue($return));

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
            ->will($this->returnValue($return));

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
            ->will($this->returnValue($return));

        $this->assertEquals(
            $return,
            $this->helper->getYouTubeApiKey()
        );
    }
}
