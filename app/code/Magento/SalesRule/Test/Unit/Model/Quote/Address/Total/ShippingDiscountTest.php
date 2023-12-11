<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Quote\Address\Total;

use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item;
use Magento\SalesRule\Model\Quote\Address\Total\ShippingDiscount;
use Magento\SalesRule\Model\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingDiscountTest extends TestCase
{
    /**
     * @var MockObject|Validator
     */
    protected $validatorMock;

    /**
     * @var MockObject|Quote
     */
    private $quoteMock;

    /**
     * @var MockObject|Total
     */
    private $totalMock;

    /**
     * @var MockObject|Address
     */
    private $addressMock;

    /**
     * @var MockObject|ShippingAssignmentInterface
     */
    private $shippingAssignmentMock;

    /**
     * @var ShippingDiscount
     */
    private $discount;

    protected function setUp(): void
    {
        $this->validatorMock = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'reset',
                    'processShippingAmount',
                ]
            )
            ->getMock();
        $this->quoteMock = $this->createMock(Quote::class);
        $this->totalMock = $this->getMockBuilder(Total::class)
            ->addMethods(
                [
                    'getDiscountAmount',
                    'getDiscountDescription',
                    'setShippingDiscountAmount',
                    'setBaseShippingDiscountAmount',
                    'getSubtotal',
                    'setSubtotalWithDiscount',
                    'setBaseSubtotalWithDiscount',
                    'getBaseSubtotal',
                    'getBaseDiscountAmount',
                    'setDiscountDescription'
                ]
            )
            ->onlyMethods(['addTotalAmount', 'addBaseTotalAmount'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressMock = $this->getMockBuilder(Address::class)
            ->addMethods(
                [
                    'getShippingAmount',
                    'getShippingDiscountAmount',
                    'getBaseShippingDiscountAmount',
                    'setShippingDiscountAmount',
                    'setBaseShippingDiscountAmount',
                    'getDiscountDescription',
                    'setDiscountAmount',
                ]
            )
            ->onlyMethods(['getQuote', 'setBaseDiscountAmount'])
            ->disableOriginalConstructor()
            ->getMock();

        $shipping = $this->getMockForAbstractClass(ShippingInterface::class);
        $shipping->expects($this->any())->method('getAddress')->willReturn($this->addressMock);
        $this->shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        $this->shippingAssignmentMock->expects($this->any())->method('getShipping')->willReturn($shipping);

        $this->discount = new ShippingDiscount(
            $this->validatorMock
        );
    }

    /**
     * Test collect with the quote has no shipping amount discount
     */
    public function testCollectNoShippingAmount()
    {
        $itemNoDiscount = $this->createMock(Item::class);

        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);

        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn(0);

        $this->shippingAssignmentMock->expects($this->any())->method('getItems')
            ->willReturn([$itemNoDiscount]);

        $this->addressMock->expects($this->once())->method('setShippingDiscountAmount')
            ->with(0)
            ->willReturnSelf();
        $this->addressMock->expects($this->once())->method('setBaseShippingDiscountAmount')
            ->with(0)
            ->willReturnSelf();

        /* Assert Collect function */
        $this->assertInstanceOf(
            ShippingDiscount::class,
            $this->discount->collect($this->quoteMock, $this->shippingAssignmentMock, $this->totalMock)
        );
    }

    /**
     * Test collect with the quote has shipping amount discount
     */
    public function testCollectWithShippingAmountDiscount()
    {
        $shippingAmount = 100;
        $shippingDiscountAmount = 50;
        $baseShippingDiscountAmount = 50;
        $discountDescription = 'Discount $50';
        $subTotal = 200;
        $discountAmount = -100;
        $baseSubTotal = 200;
        $baseDiscountAmount = -100;

        $itemNoDiscount = $this->createMock(Item::class);

        $this->addressMock->expects($this->any())->method('getQuote')->willReturn($this->quoteMock);

        $this->addressMock->expects($this->any())->method('getShippingAmount')->willReturn($shippingAmount);

        $this->addressMock->expects($this->any())->method('getShippingDiscountAmount')
            ->willReturn($shippingDiscountAmount);
        $this->addressMock->expects($this->any())->method('getBaseShippingDiscountAmount')
            ->willReturn($baseShippingDiscountAmount);

        $this->addressMock->expects($this->any())->method('getDiscountDescription')
            ->willReturn($discountDescription);

        $this->shippingAssignmentMock->expects($this->any())->method('getItems')
            ->willReturn([$itemNoDiscount]);

        $this->totalMock->expects($this->once())->method('addTotalAmount')
            ->with('discount', -$shippingDiscountAmount)->willReturnSelf();
        $this->totalMock->expects($this->once())->method('addBaseTotalAmount')
            ->with('discount', -$baseShippingDiscountAmount)->willReturnSelf();

        $this->totalMock->expects($this->once())->method('setShippingDiscountAmount')
            ->with($shippingDiscountAmount)->willReturnSelf();
        $this->totalMock->expects($this->once())->method('setBaseShippingDiscountAmount')
            ->with($baseShippingDiscountAmount)->willReturnSelf();

        $this->totalMock->expects($this->any())->method('getSubtotal')
            ->willReturn($subTotal);
        $this->totalMock->expects($this->any())->method('getDiscountAmount')
            ->willReturn($discountAmount);

        $this->totalMock->expects($this->any())->method('getBaseSubtotal')
            ->willReturn($baseSubTotal);
        $this->totalMock->expects($this->any())->method('getBaseDiscountAmount')
            ->willReturn($baseDiscountAmount);

        $this->totalMock->expects($this->once())->method('setDiscountDescription')
            ->with($discountDescription)->willReturnSelf();

        $this->totalMock->expects($this->once())->method('setSubtotalWithDiscount')
            ->with(100)->willReturnSelf();
        $this->totalMock->expects($this->once())->method('setBaseSubtotalWithDiscount')
            ->with(100)->willReturnSelf();

        $this->addressMock->expects($this->once())->method('setDiscountAmount')
            ->with($discountAmount)->willReturnSelf();

        $this->addressMock->expects($this->once())->method('setBaseDiscountAmount')
            ->with($baseDiscountAmount)->willReturnSelf();

        /* Assert Collect function */
        $this->assertInstanceOf(
            ShippingDiscount::class,
            $this->discount->collect($this->quoteMock, $this->shippingAssignmentMock, $this->totalMock)
        );
    }

    /**
     * Test fetch function with discount = 100
     */
    public function testFetch()
    {
        $discountAmount = 100;
        $discountDescription = 100;
        $expectedResult = [
            'code' => 'discount',
            'value' => 100,
            'title' => __('Discount (%1)', $discountDescription)
        ];
        $this->totalMock->expects($this->once())->method('getDiscountAmount')
            ->willReturn($discountAmount);
        $this->totalMock->expects($this->once())->method('getDiscountDescription')
            ->willReturn($discountDescription);
        $this->assertEquals($expectedResult, $this->discount->fetch($this->quoteMock, $this->totalMock));
    }
}
