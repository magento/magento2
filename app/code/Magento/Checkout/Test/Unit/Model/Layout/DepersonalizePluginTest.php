<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Checkout\Test\Unit\Model\Layout;

/**
 * Class DepersonalizePluginTest
 */
class DepersonalizePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Layout\DepersonalizePluginTest
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

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
        $this->checkoutSessionMock = $this->getMock(
            \Magento\Framework\Session\Generic::class,
            ['clearStorage', 'setData', 'getData'],
            [],
            '',
            false
        );
        $this->checkoutSessionMock = $this->getMock(
            \Magento\Checkout\Model\Session::class,
            ['clearStorage'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->moduleManagerMock = $this->getMock(\Magento\Framework\Module\Manager::class, [], [], '', false);
        $this->cacheConfigMock = $this->getMock(\Magento\PageCache\Model\Config::class, [], [], '', false);
        $this->depersonalizeCheckerMock = $this->getMock(
            \Magento\PageCache\Model\DepersonalizeChecker::class,
            [],
            [],
            '',
            false
        );

        $this->plugin = new \Magento\Checkout\Model\Layout\DepersonalizePlugin(
            $this->depersonalizeCheckerMock,
            $this->checkoutSessionMock
        );
    }

    /**
     * Test method afterGenerateXml
     */
    public function testAfterGenerateXml()
    {
        $expectedResult = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(true);
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('clearStorage')
            ->will($this->returnValue($expectedResult));

        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testAfterGenerateXmlNoDepersonalize()
    {
        $expectedResult = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('clearStorage')
            ->will($this->returnValue($expectedResult));

        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
