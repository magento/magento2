<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\PageCache\Test\Unit\Model\Layout;

/**
 * Class DepersonalizePluginTest
 */
class DepersonalizePluginTest extends \PHPUnit_Framework_TestCase
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
        $this->layoutMock = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $this->messageSessionMock = $this->getMock(
            \Magento\Framework\Message\Session::class,
            ['clearStorage'],
            [],
            '',
            false
        );
        $this->depersonalizeCheckerMock = $this->getMock(
            \Magento\PageCache\Model\DepersonalizeChecker::class,
            [],
            [],
            '',
            false
        );
        $this->plugin = new \Magento\PageCache\Model\Layout\DepersonalizePlugin(
            $this->depersonalizeCheckerMock,
            $this->eventManagerMock,
            $this->messageSessionMock
        );
    }

    public function testAfterGenerateXml()
    {
        $expectedResult = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);

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

        $expectedResult = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);
        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
