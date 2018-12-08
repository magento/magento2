<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Model\Layout;

/**
 * Class DepersonalizePluginTest
 */
class DepersonalizePluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\PageCache\Model\Layout\DepersonalizePlugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Message\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageSessionMock;

    /**
     * @var \Magento\PageCache\Model\DepersonalizeChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $depersonalizeCheckerMock;

    /**
     * SetUp
     */
    protected function setUp()
    {
        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $this->messageSessionMock = $this->createPartialMock(
            \Magento\Framework\Message\Session::class,
            ['clearStorage']
        );
        $this->depersonalizeCheckerMock = $this->createMock(\Magento\PageCache\Model\DepersonalizeChecker::class);
        $this->plugin = new \Magento\PageCache\Model\Layout\DepersonalizePlugin(
            $this->depersonalizeCheckerMock,
            $this->eventManagerMock,
            $this->messageSessionMock
        );
    }

    public function testAfterGenerateXml()
    {
        $expectedResult = $this->createMock(\Magento\Framework\View\Layout::class);

        $this->eventManagerMock->expects($this->once())
            ->method('dispatch')
            ->with($this->equalTo('depersonalize_clear_session'));
        $this->messageSessionMock->expects($this->once())->method('clearStorage');
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);

        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testAfterGenerateXmlNoDepersonalize()
    {
        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->eventManagerMock->expects($this->never())
            ->method('dispatch');
        $this->messageSessionMock->expects($this->never())->method('clearStorage');

        $expectedResult = $this->createMock(\Magento\Framework\View\Layout::class);
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
