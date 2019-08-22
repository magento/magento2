<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Checkout\Shipping;

class PriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Block\Checkout\Shipping\Price
     */
    protected $priceObj;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder(
            \Magento\Framework\Pricing\PriceCurrencyInterface::class
        )->getMock();

        $this->store = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup', 'getCustomerTaxClassId'])
            ->getMock();

        $this->quote->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));

        $checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $checkoutSession->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));

        $this->taxHelper = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingPrice', 'displayShippingPriceIncludingTax', 'displayShippingBothPrices',
            ])
            ->getMock();

        $this->priceObj = $objectManager->getObject(
            \Magento\Tax\Block\Checkout\Shipping\Price::class,
            [
                'checkoutSession' => $checkoutSession,
                'taxHelper' => $this->taxHelper,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
    }

    /**
     * @param float $shippingPrice
     * @return \Magento\Quote\Model\Quote\Address\Rate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function setupShippingRate($shippingPrice)
    {
        $shippingRateMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice', '__wakeup'])
            ->getMock();
        $shippingRateMock->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($shippingPrice));
        return $shippingRateMock;
    }

    public function testGetShippingPriceExclTax()
    {
        $shippingPrice = 5;
        $shippingPriceExclTax = 4.5;
        $convertedPrice = "$4.50";

        $shippingRateMock = $this->setupShippingRate($shippingPrice);

        $this->taxHelper->expects($this->once())
            ->method('getShippingPrice')
            ->will($this->returnValue($shippingPriceExclTax));

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($this->logicalOr($shippingPriceExclTax, true, $this->store))
            ->willReturn($convertedPrice);

        $this->priceObj->setShippingRate($shippingRateMock);
        $this->assertEquals($convertedPrice, $this->priceObj->getShippingPriceExclTax());
    }

    public function testGetShippingPriceInclTax()
    {
        $shippingPrice = 5;
        $shippingPriceInclTax = 5.5;
        $convertedPrice = "$5.50";

        $shippingRateMock = $this->setupShippingRate($shippingPrice);

        $this->taxHelper->expects($this->once())
            ->method('getShippingPrice')
            ->will($this->returnValue($shippingPriceInclTax));

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($this->logicalOr($shippingPriceInclTax, true, $this->store))
            ->will($this->returnValue($convertedPrice));

        $this->priceObj->setShippingRate($shippingRateMock);
        $this->assertEquals($convertedPrice, $this->priceObj->getShippingPriceExclTax());
    }

    public function testDisplayShippingPriceInclTax()
    {
        $this->taxHelper->expects($this->once())
            ->method('displayShippingPriceIncludingTax');

        $this->priceObj->displayShippingPriceInclTax();
    }

    public function testDisplayShippingBothPrices()
    {
        $this->taxHelper->expects($this->once())
            ->method('displayShippingBothPrices');

        $this->priceObj->displayShippingBothPrices();
    }
}
