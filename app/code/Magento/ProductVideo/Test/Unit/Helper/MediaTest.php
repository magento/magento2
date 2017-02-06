<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Helper;

use Magento\ProductVideo\Helper\Media;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    /**
     * @var \Magento\ProductVideo\Helper\Media|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * Create mock objects
     */
    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();
        $this->contextMock = $this->getMock(\Magento\Framework\App\Helper\Context::class, [], [], '', false);
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
