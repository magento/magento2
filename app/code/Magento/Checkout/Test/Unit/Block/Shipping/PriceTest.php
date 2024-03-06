<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Block\Shipping;

use Magento\Checkout\Block\Shipping\Price;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Store\Model\Store;
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
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->priceCurrency = $this->getMockBuilder(
            PriceCurrencyInterface::class
        )->getMock();

        $this->priceObj = $objectManager->getObject(
            Price::class,
            ['priceCurrency'   => $this->priceCurrency]
        );
    }

    public function testGetShippingPrice()
    {
        $shippingPrice = 5;
        $convertedPrice = "$5";

        $shippingRateMock = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPrice'])
            ->getMock();
        $shippingRateMock->expects($this->once())
            ->method('getPrice')
            ->willReturn($shippingPrice);

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($shippingPrice, true, true)
            ->willReturn($convertedPrice);

        $this->priceObj->setShippingRate($shippingRateMock);
        $this->assertEquals($convertedPrice, $this->priceObj->getShippingPrice());
    }
}
