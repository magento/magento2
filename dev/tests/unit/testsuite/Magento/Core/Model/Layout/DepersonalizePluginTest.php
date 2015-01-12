<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Core\Model\Layout;

/**
 * Class DepersonalizePluginTest
 */
class DepersonalizePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Layout\DepersonalizePluginTest
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheConfigMock;

    /**
     * @var \Magento\Framework\Message\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageSessionMock;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->moduleManagerMock = $this->getMock('Magento\Framework\Module\Manager', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\Manager', [], [], '', false);
        $this->cacheConfigMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);
        $this->messageSessionMock = $this->getMock('Magento\Framework\Message\Session',
            ['clearStorage'],
            [],
            '',
            false
        );
        $this->plugin = new DepersonalizePlugin(
            $this->requestMock,
            $this->moduleManagerMock,
            $this->eventManagerMock,
            $this->cacheConfigMock,
            $this->messageSessionMock
        );
    }

    /**
     * Test method afterGenerateXml with enabled module PageCache
     */
    public function testAfterGenerateXmlPageCacheEnabled()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->once($this->once()))
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('depersonalize_clear_session'));
        $this->messageSessionMock->expects($this->once())
            ->method('clearStorage');

        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Test method afterGenerateXml with disabled module PageCache
     */
    public function testAfterGenerateXmlPageCacheDisabled()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(false));
        $this->requestMock->expects($this->never())
            ->method('isAjax')
            ->will($this->returnValue(false));
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
