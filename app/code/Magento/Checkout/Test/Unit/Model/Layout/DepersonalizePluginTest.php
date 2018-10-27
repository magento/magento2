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
class DepersonalizePluginTest extends \PHPUnit\Framework\TestCase
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
        $this->layoutMock = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->checkoutSessionMock = $this->createPartialMock(\Magento\Framework\Session\Generic::class, ['clearStorage', 'setData', 'getData']);
        $this->checkoutSessionMock = $this->createPartialMock(\Magento\Checkout\Model\Session::class, ['clearStorage']);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->moduleManagerMock = $this->createMock(\Magento\Framework\Module\Manager::class);
        $this->cacheConfigMock = $this->createMock(\Magento\PageCache\Model\Config::class);
        $this->depersonalizeCheckerMock = $this->createMock(\Magento\PageCache\Model\DepersonalizeChecker::class);

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
        $expectedResult = $this->createMock(\Magento\Framework\View\Layout::class);

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
        $expectedResult = $this->createMock(\Magento\Framework\View\Layout::class);

        $this->depersonalizeCheckerMock->expects($this->once())->method('checkIfDepersonalize')->willReturn(false);
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('clearStorage')
            ->will($this->returnValue($expectedResult));

        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
