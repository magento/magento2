<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrderAdapterTest extends TestCase
{
    /** @var OrderAdapter */
    protected $model;

    /**
     * @var OrderInterface|MockObject
     */
    protected $orderMock;

    /**
     * @var AddressAdapterFactory|MockObject
     */
    protected $addressAdapterFactoryMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressAdapterFactoryMock =
            $this->getMockBuilder(AddressAdapterFactory::class)
                ->onlyMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();

        $this->model = new OrderAdapter($this->orderMock, $this->addressAdapterFactoryMock);
    }

    public function testGetCurrencyCode()
    {
        $expected = 'USD';
        $this->orderMock->expects($this->once())->method('getBaseCurrencyCode')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCurrencyCode());
    }

    public function testGetOrderIncrementId()
    {
        $expected = '1';
        $this->orderMock->expects($this->once())->method('getIncrementId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getOrderIncrementId());
    }

    public function testGetCustomerId()
    {
        $expected = 1;
        $this->orderMock->expects($this->once())->method('getCustomerId')->willReturn($expected);
        $this->assertEquals($expected, $this->model->getCustomerId());
    }

    public function testGetBillingAddressIsNull()
    {
        $this->orderMock->expects($this->once())->method('getBillingAddress')->willReturn(null);

        $this->assertNull($this->model->getBillingAddress());
    }

    public function testGetBillingAddress()
    {
        /** @var AddressAdapterInterface $addressAdapterMock */
        $addressAdapterMock = $this->getMockBuilder(AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var OrderAddressInterface $orderAddressMock */
        $orderAddressMock = $this->getMockBuilder(OrderAddressInterface::class)
            ->getMockForAbstractClass();
        $this->addressAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->with(['address' => $orderAddressMock])
            ->willReturn($addressAdapterMock);
        $this->orderMock->expects($this->exactly(2))->method('getBillingAddress')->willReturn($orderAddressMock);

        $this->assertSame($addressAdapterMock, $this->model->getBillingAddress());
    }

    public function testGetShippingAddressIsNull()
    {
        $this->orderMock->expects($this->once())->method('getShippingAddress')->willReturn(null);

        $this->assertNull($this->model->getShippingAddress());
    }

    public function testGetShippingAddress()
    {
        /** @var AddressAdapterInterface $addressAdapterMock */
        $addressAdapterMock = $this->getMockBuilder(AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var OrderAddressInterface $orderAddressMock */
        $orderAddressMock = $this->getMockBuilder(OrderAddressInterface::class)
            ->getMockForAbstractClass();
        $this->addressAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->with(['address' => $orderAddressMock])
            ->willReturn($addressAdapterMock);
        $this->orderMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($orderAddressMock);

        $this->assertSame($addressAdapterMock, $this->model->getShippingAddress());
    }
}
