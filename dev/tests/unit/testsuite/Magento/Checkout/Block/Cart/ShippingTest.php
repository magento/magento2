<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $objectManager;

    protected $shippingBlock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetShippingPriceHtml()
    {
        $shippingRateMock = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address\Rate')
            ->disableOriginalConstructor()
            ->getMock();

        $shippingPriceHtml = "$3.25 ($3.56 Incl Tax)";

        $priceBlockMock = $this->getMockBuilder('\Magento\Checkout\Block\Shipping\Price')
            ->disableOriginalConstructor()
            ->setMethods(['setShippingRate', 'toHtml'])
            ->getMock();

        $priceBlockMock->expects($this->once())
            ->method('setShippingRate')
            ->with($shippingRateMock);

        $priceBlockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue($shippingPriceHtml));

        $layoutMock = $this->getMockBuilder('\Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $layoutMock->expects($this->once())
            ->method('getBlock')
            ->with('checkout.shipping.price')
            ->will($this->returnValue($priceBlockMock));

        $contextMock = $this->getMockBuilder('\Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMock();

        $contextMock->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($layoutMock));

        /** @var \Magento\Checkout\Block\Cart\Shipping $shippingBlock */
        $shippingBlock = $this->objectManager->getObject(
            'Magento\Checkout\Block\Cart\Shipping',
            ['context' => $contextMock]
        );

        $this->assertEquals($shippingPriceHtml, $shippingBlock->getShippingPriceHtml($shippingRateMock));
    }
}
