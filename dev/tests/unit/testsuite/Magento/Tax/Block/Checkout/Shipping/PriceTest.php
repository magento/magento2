<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Block\Checkout\Shipping;


class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Checkout\Shipping\Price
     */
    protected $priceObj;

    /**
     * @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
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
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder('Magento\Framework\Pricing\PriceCurrencyInterface')->getMock();

        $this->store = $this->getMockBuilder('Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder('Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup', 'getCustomerTaxClassId'])
            ->getMock();

        $this->quote->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));

        $checkoutSession = $this->getMockBuilder('\Magento\Checkout\Model\Session')
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $checkoutSession->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));

        $this->taxHelper = $this->getMockBuilder('\Magento\Tax\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingPrice', 'displayShippingPriceIncludingTax', 'displayShippingBothPrices',
            ])
            ->getMock();

        $this->priceObj = $objectManager->getObject(
            'Magento\Tax\Block\Checkout\Shipping\Price',
            [
                'checkoutSession' => $checkoutSession,
                'taxHelper' => $this->taxHelper,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
    }

    /**
     * @param float $shippingPrice
     * @return \Magento\Sales\Model\Quote\Address\Rate|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function setupShippingRate($shippingPrice)
    {
        $shippingRateMock = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address\Rate')
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
