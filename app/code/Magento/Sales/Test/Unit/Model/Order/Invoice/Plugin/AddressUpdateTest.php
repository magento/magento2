<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Plugin;

class AddressUpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Invoice\Plugin\AddressUpdate
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $gripPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    protected function setUp()
    {
        $this->gripPoolMock = $this->createMock(\Magento\Sales\Model\ResourceModel\GridPool::class);
        $this->attributeMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Attribute::class);
        $this->model = new \Magento\Sales\Model\Order\Invoice\Plugin\AddressUpdate(
            $this->gripPoolMock,
            $this->attributeMock
        );
    }

    public function testAfterProcess()
    {
        $billingId = 100;
        $shippingId = 200;
        $orderId = 50;

        $orderMock = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['hasInvoices', 'getBillingAddress', 'getShippingAddress', 'getInvoiceCollection', 'getId']
        );

        $shippingMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $shippingMock->expects($this->once())->method('getId')->willReturn($shippingId);

        $billingMock = $this->createMock(\Magento\Sales\Model\Order\Address::class);
        $billingMock->expects($this->once())->method('getId')->willReturn($billingId);

        $invoiceCollectionMock = $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class);
        $invoiceMock = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $invoiceCollectionMock->expects($this->once())->method('getItems')->willReturn([$invoiceMock]);

        $orderMock->expects($this->once())->method('hasInvoices')->willReturn(true);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingMock);
        $orderMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingMock);
        $orderMock->expects($this->once())->method('getInvoiceCollection')->willReturn($invoiceCollectionMock);
        $orderMock->expects($this->once())->method('getId')->willReturn($orderId);

        $invoiceMock->expects($this->once())->method('getBillingAddressId')->willReturn(null);
        $invoiceMock->expects($this->once())->method('getShippingAddressId')->willReturn(null);
        $invoiceMock->expects($this->once())->method('setShippingAddressId')->with($shippingId)->willReturnSelf();
        $invoiceMock->expects($this->once())->method('setBillingAddressId')->with($billingId)->willReturnSelf();

        $this->attributeMock->expects($this->once())
            ->method('saveAttribute')
            ->with($invoiceMock, ['billing_address_id', 'shipping_address_id'])
            ->willReturnSelf();

        $this->gripPoolMock->expects($this->once())->method('refreshByOrderId')->with($orderId)->willReturnSelf();

        $this->model->afterProcess(
            $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Handler\Address::class),
            $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Handler\Address::class),
            $orderMock
        );
    }
}
