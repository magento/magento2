<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;

/**
 * Class OrderAdapterTest
 */
class OrderAdapterTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderAdapter */
    protected $model;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Payment\Gateway\Data\Order\AddressAdapterFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressAdapterFactoryMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressAdapterFactoryMock =
            $this->getMockBuilder(\Magento\Payment\Gateway\Data\Order\AddressAdapterFactory::class)
                ->setMethods(['create'])
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
        $addressAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $orderAddressMock */
        $orderAddressMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderAddressInterface::class)
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
        $addressAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)
            ->getMockForAbstractClass();
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $orderAddressMock */
        $orderAddressMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderAddressInterface::class)
            ->getMockForAbstractClass();
        $this->addressAdapterFactoryMock->expects($this->once())
            ->method('create')
            ->with(['address' => $orderAddressMock])
            ->willReturn($addressAdapterMock);
        $this->orderMock->expects($this->exactly(2))->method('getShippingAddress')->willReturn($orderAddressMock);

        $this->assertSame($addressAdapterMock, $this->model->getShippingAddress());
    }
}
