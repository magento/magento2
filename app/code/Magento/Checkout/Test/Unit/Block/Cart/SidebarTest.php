<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetTotalsHtml()
    {
        $totalsHtml = "$134.36";
        $totalsBlockMock = $this->getMockBuilder('\Magento\Checkout\Block\Shipping\Price')
            ->disableOriginalConstructor()
            ->setMethods(['toHtml'])
            ->getMock();

        $totalsBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($totalsHtml));

        $layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('checkout.cart.minicart.totals')
            ->will($this->returnValue($totalsBlockMock));

        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        /** @var \Magento\Checkout\Block\Cart\Sidebar $sidebarBlock */
        $sidebarBlock = $this->_objectManager->getObject(
            'Magento\Checkout\Block\Cart\Sidebar',
            ['context' => $contextMock]
        );

        $this->assertEquals($totalsHtml, $sidebarBlock->getTotalsHtml());
    }
}
