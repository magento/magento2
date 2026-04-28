<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Shipping;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Shipping
     */
    protected $shippingModel;

    /**
     * @var Quote|MockObject
     */
    protected $quote;

    /**
     * @var Total|MockObject
     */
    protected $total;

    /**
     * @var ShippingAssignmentInterface|MockObject
     */
    protected $shippingAssignment;

    /**
     * @var Address|MockObject
     */
    protected $address;

    /**
     * @var ShippingInterface|MockObject
     */
    protected $shipping;

    /**
     * @var FreeShippingInterface|MockObject
     */
    protected $freeShipping;

    /**
     * @var CartItemInterface|MockObject
     */
    protected $cartItem;

    /**
     * @var Rate|MockObject
     */
    protected $rate;

    /**
     * @var Store|MockObject
     */
    protected $store;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrency;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->freeShipping = $this->createMock(FreeShippingInterface::class);
        $this->priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $objectManager = new ObjectManager($this);
        $this->shippingModel = $objectManager->getObject(
            Shipping::class,
            [
                'freeShipping' => $this->freeShipping,
                'priceCurrency' => $this->priceCurrency
            ]
        );

        $this->quote = $this->createMock(Quote::class);
        $this->total = new Total([], new Json());
        $this->shippingAssignment = $this->createMock(ShippingAssignmentInterface::class);
        $this->address = $this->createPartialMockWithReflection(
            Address::class,
            ['collectShippingRates', 'getAllShippingRates', 'getItemQty', 'setFreeShipping']
        );
        $this->shipping = $this->createMock(ShippingInterface::class);
        $this->cartItem = $this->createPartialMockWithReflection(
            CartItemInterface::class,
            [
                'getIsVirtual', 'getParentItem', 'getHasChildren', 'getWeight', 'getQty', 'setQty', 'getProduct',
                'getRowWeight', 'setRowWeight', 'getItemId', 'setItemId', 'getSku', 'setSku', 'getProductType',
                'setProductType', 'getQuoteId', 'setQuoteId', 'getPrice', 'setPrice', 'getName', 'setName',
                'getProductOption', 'setProductOption', 'getExtensionAttributes', 'setExtensionAttributes',
                'setFreeShipping', 'getFreeShipping'
            ]
        );
        $this->rate = $this->createPartialMockWithReflection(Rate::class, ['getCode', 'getPrice']);
        $this->store = $this->createMock(Store::class);
    }

    /**
     * @return void
     */
    public function testFetch(): void
    {
        $shippingAmount = 100;
        $shippingDescription = 100;
        $expectedResult = [
            'code' => 'shipping',
            'value' => 100,
            'title' => __('Shipping & Handling (%1)', $shippingDescription)
        ];

        $quoteMock = $this->createMock(Quote::class);
        $totalMock = new Total([], new Json());
        $totalMock->setShippingAmount($shippingAmount);
        $totalMock->setShippingDescription($shippingDescription);

        $this->assertEquals($expectedResult, $this->shippingModel->fetch($quoteMock, $totalMock));
    }

    /**
     * @return void
     */
    public function testCollect(): void
    {
        $this->shippingAssignment->expects($this->exactly(3))
            ->method('getShipping')
            ->willReturn($this->shipping);
        $this->shipping->expects($this->exactly(2))
            ->method('getAddress')
            ->willReturn($this->address);
        $this->shipping->expects($this->once())
            ->method('getMethod')
            ->willReturn('flatrate');
        $this->shippingAssignment->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn([$this->cartItem]);
        $isFreeShipping = true;
        $this->freeShipping
            ->expects($this->once())
            ->method('isFreeShipping')
            ->with($this->quote, [$this->cartItem])
            ->willReturn($isFreeShipping);
        // setFreeShipping is a real method on helper; verify via state after collect
        // setTotalAmount and setBaseTotalAmount are real methods on Total helper
        $product = $this->createMock(Product::class);
        $this->cartItem->method('getIsVirtual')->willReturn(false);
        $this->cartItem->method('getParentItem')->willReturn(null);
        $this->cartItem->method('getHasChildren')->willReturn(false);
        $this->cartItem->method('getWeight')->willReturn(2);
        $this->cartItem->method('getQty')->willReturn(2);
        $this->cartItem->method('getProduct')->willReturn($product);
        $this->cartItem->method('getFreeShipping')->willReturn(false);
        $this->freeShippingAssertions();
        $this->cartItem->method('getRowWeight')->willReturn(0);
        $this->address->method('getItemQty')->willReturn(2);
        $this->address->expects($this->once())
            ->method('collectShippingRates');
        $this->address->expects($this->once())
            ->method('getAllShippingRates')
            ->willReturn([$this->rate]);
        $this->rate->method('getCode')->willReturn('flatrate');
        $this->quote->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->rate->method('getPrice')->willReturn(5);
        $this->priceCurrency->expects($this->once())
            ->method('convert')
            ->with(5, $this->store)
            ->willReturn(10);
        // amounts will be set on helper via setTotalAmount/setBaseTotalAmount
        $this->rate->setCarrierTitle('Carrier title');
        $this->rate->setMethodTitle('Method title');
        // description will be set on helper via setShippingDescription

        $this->shippingModel->collect($this->quote, $this->shippingAssignment, $this->total);

        // Assert helper state instead of mocking methods on helper
        $this->assertEquals(10, $this->total->getData('shipping_amount'));
        $this->assertEquals(5, $this->total->getData('base_shipping_amount'));
        $this->assertEquals('Carrier title - Method title', $this->total->getShippingDescription());
    }

    /**
     * @return void
     */
    protected function freeShippingAssertions(): void
    {
        $this->address->setFreeShipping(true);
        $this->cartItem->setFreeShipping(true);
    }
}
