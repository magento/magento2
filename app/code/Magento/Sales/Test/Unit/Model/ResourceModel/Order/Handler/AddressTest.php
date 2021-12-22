<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Sales\Model\ResourceModel\Order\Address\Collection;
use Magento\Sales\Model\ResourceModel\Order\Handler\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    protected $address;

    /**
     * @var Collection|MockObject
     */
    protected $addressCollectionMock;

    /**
     * @var Attribute|MockObject
     */
    protected $attributeMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Address|MockObject
     */
    protected $addressMock;

    protected function setUp(): void
    {
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->addMethods(
                [
                    'hasBillingAddressId',
                    'unsBillingAddressId',
                    'hasShippingAddressId',
                    'getShippingAddressId',
                    'setShippingAddressId',
                    'unsShippingAddressId'
                ]
            )
            ->onlyMethods(
                [
                    'getAddresses',
                    'save',
                    'getBillingAddress',
                    'getShippingAddress',
                    'getBillingAddressId',
                    'setBillingAddressId'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $this->addressCollectionMock = $this->createMock(
            Collection::class
        );
        $this->address = new Address(
            $this->attributeMock
        );
    }

    /**
     * Test process method with billing_address
     */
    public function testProcessBillingAddress()
    {
        $this->orderMock->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->once())
            ->method('save')->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);
        $this->addressMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(2);
        $this->orderMock->expects($this->once())
            ->method('getBillingAddressId')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('setBillingAddressId')->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn(null);
        $this->attributeMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->orderMock, ['billing_address_id'])->willReturnSelf();
        $this->assertEquals($this->address, $this->address->process($this->orderMock));
    }

    /**
     * Test process method with shipping_address
     */
    public function testProcessShippingAddress()
    {
        $this->orderMock->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->once())
            ->method('save')->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);
        $this->addressMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(2);
        $this->orderMock->expects($this->once())
            ->method('setShippingAddressId')->willReturnSelf();
        $this->attributeMock->expects($this->once())
            ->method('saveAttribute')
            ->with($this->orderMock, ['shipping_address_id'])->willReturnSelf();
        $this->assertEquals($this->address, $this->address->process($this->orderMock));
    }

    /**
     * Test processing of the shipping address when shipping address id was not changed.
     * setShippingAddressId and saveAttribute methods must not be executed.
     */
    public function testProcessShippingAddressNotChanged()
    {
        $this->orderMock->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->once())
            ->method('save')->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);
        $this->addressMock->expects($this->once())
            ->method('getId')->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddressId')
            ->willReturn(1);
        $this->orderMock->expects($this->never())
            ->method('setShippingAddressId')->willReturnSelf();
        $this->attributeMock->expects($this->never())
            ->method('saveAttribute')
            ->with($this->orderMock, ['shipping_address_id'])->willReturnSelf();
        $this->assertEquals($this->address, $this->address->process($this->orderMock));
    }

    /**
     * Test processing of the billing address when billing address id was not changed.
     * setBillingAddressId and saveAttribute methods must not be executed.
     */
    public function testProcessBillingAddressNotChanged()
    {
        $this->orderMock->expects($this->exactly(2))
            ->method('getAddresses')
            ->willReturn([$this->addressMock]);
        $this->addressMock->expects($this->once())
            ->method('save')->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($this->addressMock);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn(null);
        $this->addressMock->expects($this->once())
            ->method('getId')->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getBillingAddressId')
            ->willReturn(1);
        $this->orderMock->expects($this->never())
            ->method('setBillingAddressId')->willReturnSelf();
        $this->attributeMock->expects($this->never())
            ->method('saveAttribute')
            ->with($this->orderMock, ['billing_address_id'])->willReturnSelf();
        $this->assertEquals($this->address, $this->address->process($this->orderMock));
    }

    /**
     * Test method removeEmptyAddresses
     */
    public function testRemoveEmptyAddresses()
    {
        $this->orderMock->expects($this->once())
            ->method('hasBillingAddressId')
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getBillingAddressId')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('unsBillingAddressId')->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('hasShippingAddressId')
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getShippingAddressId')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('unsShippingAddressId')->willReturnSelf();
        $this->assertEquals($this->address, $this->address->removeEmptyAddresses($this->orderMock));
    }
}
