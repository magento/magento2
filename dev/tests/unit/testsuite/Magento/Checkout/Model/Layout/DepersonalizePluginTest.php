<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Model\Layout;

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
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheConfigMock;

    /**
     * SetUp
     */
    public function setUp()
    {
        $this->layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->checkoutSessionMock = $this->getMock(
            'Magento\Framework\Session\Generic',
            ['clearStorage', 'setData', 'getData'],
            [],
            '',
            false
        );
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session',
            ['clearStorage'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->moduleManagerMock = $this->getMock('Magento\Framework\Module\Manager', [], [], '', false);
        $this->cacheConfigMock = $this->getMock('Magento\PageCache\Model\Config', [], [], '', false);

        $this->plugin = new \Magento\Checkout\Model\Layout\DepersonalizePlugin(
            $this->checkoutSessionMock,
            $this->moduleManagerMock,
            $this->requestMock,
            $this->cacheConfigMock
        );
    }

    /**
     * Test method afterGenerateXml
     */
    public function testAfterGenerateXml()
    {
        $expectedResult = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with($this->equalTo('Magento_PageCache'))
            ->will($this->returnValue(true));
        $this->cacheConfigMock->expects($this->once())
            ->method('isEnabled')
            ->will($this->returnValue(true));
        $this->requestMock->expects($this->once($this->once()))
            ->method('isAjax')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->once())
            ->method('isCacheable')
            ->will($this->returnValue(true));

        $this->checkoutSessionMock->expects($this->once())
            ->method('clearStorage')
            ->will($this->returnValue($expectedResult));

        $actualResult = $this->plugin->afterGenerateXml($this->layoutMock, $expectedResult);
        $this->assertEquals($expectedResult, $actualResult);
    }
}
