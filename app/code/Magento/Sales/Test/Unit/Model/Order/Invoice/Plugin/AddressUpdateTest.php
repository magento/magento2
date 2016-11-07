<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Plugin;

class AddressUpdateTest extends \PHPUnit_Framework_TestCase
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
        $this->gripPoolMock = $this->getMock(\Magento\Sales\Model\ResourceModel\GridPool::class, [], [], '', false);
        $this->attributeMock = $this->getMock(\Magento\Sales\Model\ResourceModel\Attribute::class, [], [], '', false);
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

        $orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            ['hasInvoices', 'getBillingAddress', 'getShippingAddress', 'getInvoiceCollection', 'getId'],
            [],
            '',
            false
        );

        $shippingMock = $this->getMock(\Magento\Sales\Model\Order\Address::class, [], [], '', false);
        $shippingMock->expects($this->once())->method('getId')->willReturn($shippingId);

        $billingMock = $this->getMock(\Magento\Sales\Model\Order\Address::class, [], [], '', false);
        $billingMock->expects($this->once())->method('getId')->willReturn($billingId);

        $invoiceCollectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class,
            [],
            [],
            '',
            false
        );
        $invoiceMock = $this->getMock(\Magento\Sales\Model\Order\Invoice::class, [], [], '', false);
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
            $this->getMock(\Magento\Sales\Model\ResourceModel\Order\Handler\Address::class, [], [], '', false),
            $this->getMock(\Magento\Sales\Model\ResourceModel\Order\Handler\Address::class, [], [], '', false),
            $orderMock
        );
    }
}
