<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Block\Cart;

use Magento\Checkout\Block\Cart\Shipping;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Directory\Block\Data as DirectoryData;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Shipping\Model\CarrierFactoryInterface;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Shipping */
    protected $model;

    /** @var  Context |\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var  CustomerSession |\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSession;

    /** @var  CheckoutSession |\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    /** @var  DirectoryData |\PHPUnit_Framework_MockObject_MockObject */
    protected $directory;

    /** @var  CarrierFactoryInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $carrierFactory;

    /** @var  PriceCurrencyInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $priceCurrency;

    /** @var  Layout |\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    protected function setUp()
    {
        $this->prepareContext();

        $this->customerSession = $this->getMockBuilder('Magento\Customer\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->directory = $this->getMockBuilder('Magento\Directory\Block\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->carrierFactory = $this->getMockBuilder('Magento\Shipping\Model\CarrierFactoryInterface')
            ->getMockForAbstractClass();

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')
            ->getMockForAbstractClass();

        $this->model = new Shipping(
            $this->context,
            $this->customerSession,
            $this->checkoutSession,
            $this->directory,
            $this->carrierFactory,
            $this->priceCurrency
        );
    }

    protected function prepareContext()
    {
        $this->layout = $this->getMockBuilder('Magento\Framework\View\Layout')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Magento\Framework\View\Element\Template\Context')
            ->disableOriginalConstructor()
            ->setMethods([
                'getLayout',
            ])
            ->getMock();

        $this->context->expects($this->once())
            ->method('getLayout')
            ->will($this->returnValue($this->layout));

    }

    public function testGetShippingPriceHtml()
    {
        $shippingRateMock = $this->getMockBuilder('\Magento\Quote\Model\Quote\Address\Rate')
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

        $this->layout->expects($this->once())
            ->method('getBlock')
            ->with('checkout.shipping.price')
            ->will($this->returnValue($priceBlockMock));

        $this->assertEquals($shippingPriceHtml, $this->model->getShippingPriceHtml($shippingRateMock));
    }

    /**
     * @param int $count
     * @param bool $expectedResult
     * @dataProvider dataProviderIsMultipleCountriesAllowed
     */
    public function testIsMultipleCountriesAllowed(
        $count,
        $expectedResult
    ) {
        $collection = $this->getMockBuilder('Magento\Directory\Model\Resource\Country\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('count')
            ->willReturn($count);

        $this->directory->expects($this->once())
            ->method('getCountryCollection')
            ->willReturn($collection);

        $this->assertEquals($expectedResult, $this->model->isMultipleCountriesAllowed());
    }

    /**
     * @return array
     */
    public function dataProviderIsMultipleCountriesAllowed()
    {
        return [
            [0, false],
            [1, false],
            [2, true],
        ];
    }
}
