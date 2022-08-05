<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\Shipping;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingTest extends TestCase
{
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
        $this->freeShipping = $this->getMockForAbstractClass(
            FreeShippingInterface::class,
            [],
            '',
            false
        );
        $this->priceCurrency = $this->getMockForAbstractClass(
            PriceCurrencyInterface::class,
            [],
            '',
            false
        );
        $objectManager = new ObjectManager($this);
        $this->shippingModel = $objectManager->getObject(
            Shipping::class,
            [
                'freeShipping' => $this->freeShipping,
                'priceCurrency' => $this->priceCurrency
            ]
        );

        $this->quote = $this->createMock(Quote::class);
        $this->total = $this->getMockBuilder(Total::class)
            ->addMethods(['setShippingAmount', 'setBaseShippingAmount', 'setShippingDescription'])
            ->onlyMethods(['setBaseTotalAmount', 'setTotalAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAssignment = $this->getMockForAbstractClass(
            ShippingAssignmentInterface::class,
            [],
            '',
            false
        );
        $this->address = $this->getMockBuilder(Address::class)
            ->addMethods(
                [
                    'setWeight',
                    'setFreeMethodWeight',
                    'getWeight',
                    'getFreeMethodWeight',
                    'setFreeShipping',
                    'setItemQty',
                    'setShippingDescription',
                    'getShippingDescription',
                    'getFreeShipping'
                ]
            )
            ->onlyMethods(['collectShippingRates', 'getAllShippingRates'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shipping = $this->getMockForAbstractClass(
            ShippingInterface::class,
            [],
            '',
            false
        );
        $this->cartItem = $this->getMockForAbstractClass(
            CartItemInterface::class,
            [],
            '',
            false,
            false,
            true,
            [
                'getFreeShipping',
                'getProduct',
                'getParentItem',
                'getHasChildren',
                'isVirtual',
                'getWeight',
                'getQty',
                'setRowWeight'
            ]
        );
        $this->rate = $this->getMockBuilder(Rate::class)
            ->addMethods(['getPrice', 'getCode', 'getCarrierTitle', 'getMethodTitle'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(['getShippingAmount', 'getShippingDescription'])
            ->disableOriginalConstructor()
            ->getMock();

        $totalMock->expects($this->once())->method('getShippingAmount')->willReturn($shippingAmount);
        $totalMock->expects($this->once())->method('getShippingDescription')->willReturn($shippingDescription);
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
        $this->address
            ->expects($this->once())
            ->method('setFreeShipping')
            ->with((int)$isFreeShipping);
        $this->total->expects($this->atLeastOnce())
            ->method('setTotalAmount');
        $this->total->expects($this->atLeastOnce())
            ->method('setBaseTotalAmount');
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturnSelf();
        $this->cartItem->expects($this->atLeastOnce())
            ->method('isVirtual')
            ->willReturn(false);
        $this->cartItem->method('getParentItem')
            ->willReturn(false);
        $this->cartItem->method('getHasChildren')
            ->willReturn(false);
        $this->cartItem->method('getWeight')
            ->willReturn(2);
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(2);
        $this->freeShippingAssertions();
        $this->cartItem->method('setRowWeight')
            ->with(0);
        $this->address->method('setItemQty')
            ->with(2);
        $this->address->expects($this->atLeastOnce())
            ->method('setWeight');
        $this->address->expects($this->atLeastOnce())
            ->method('setFreeMethodWeight');
        $this->address->expects($this->once())
            ->method('collectShippingRates');
        $this->address->expects($this->once())
            ->method('getAllShippingRates')
            ->willReturn([$this->rate]);
        $this->rate->expects($this->once())
            ->method('getCode')
            ->willReturn('flatrate');
        $this->quote->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->rate->expects($this->atLeastOnce())
            ->method('getPrice')
            ->willReturn(5);
        $this->priceCurrency->expects($this->once())
            ->method('convert')
            ->with(5, $this->store)
            ->willReturn(10);
        $this->total->expects($this->once())
            ->method('setShippingAmount')
            ->with(10);
        $this->total->expects($this->once())
            ->method('setBaseShippingAmount')
            ->with(5);
        $this->rate->expects($this->once())
            ->method('getCarrierTitle')
            ->willReturn('Carrier title');
        $this->rate->expects($this->once())
            ->method('getMethodTitle')
            ->willReturn('Method title');
        $this->address->expects($this->once())
            ->method('setShippingDescription')
            ->with('Carrier title - Method title');
        $this->address->expects($this->once())
            ->method('getShippingDescription')
            ->willReturn('Carrier title - Method title');
        $this->total->expects($this->once())
            ->method('setShippingDescription')
            ->with('Carrier title - Method title');

        $this->shippingModel->collect($this->quote, $this->shippingAssignment, $this->total);
    }

    /**
     * @return void
     */
    protected function freeShippingAssertions(): void
    {
        $this->address
            ->method('getFreeShipping')
            ->willReturnOnConsecutiveCalls(false, true);
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getFreeShipping')
            ->willReturn(true);
    }
}
