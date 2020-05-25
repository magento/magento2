<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Block\Checkout\Shipping;

use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Store\Model\Store;
use Magento\Tax\Block\Checkout\Shipping\Price;
use Magento\Tax\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceTest extends TestCase
{
    /**
     * @var Price
     */
    protected $priceObj;

    /**
     * @var Quote|MockObject
     */
    protected $quote;

    /**
     * @var Store|MockObject
     */
    protected $store;

    /**
     * @var Data|MockObject
     */
    protected $taxHelper;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', '__wakeup', 'getCustomerTaxClassId'])
            ->getMock();

        $this->quote->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);

        $checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuote', '__wakeup'])
            ->getMock();

        $checkoutSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->taxHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getShippingPrice', 'displayShippingPriceIncludingTax', 'displayShippingBothPrices',
            ])
            ->getMock();

        $this->priceObj = $objectManager->getObject(
            Price::class,
            [
                'checkoutSession' => $checkoutSession,
                'taxHelper' => $this->taxHelper,
                'priceCurrency' => $this->priceCurrency,
            ]
        );
    }

    /**
     * @param float $shippingPrice
     * @return Rate|MockObject
     */
    protected function setupShippingRate($shippingPrice)
    {
        $shippingRateMock = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPrice', '__wakeup'])
            ->getMock();
        $shippingRateMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($shippingPrice);
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
            ->willReturn($shippingPriceExclTax);

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
            ->willReturn($shippingPriceInclTax);

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($this->logicalOr($shippingPriceInclTax, true, $this->store))
            ->willReturn($convertedPrice);

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
