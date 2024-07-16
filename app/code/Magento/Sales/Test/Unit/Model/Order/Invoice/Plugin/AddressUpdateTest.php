<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Plugin\AddressUpdate;
use Magento\Sales\Model\ResourceModel\Attribute;
use Magento\Sales\Model\ResourceModel\GridPool;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddressUpdateTest extends TestCase
{
    /**
     * @var AddressUpdate
     */
    private $model;

    /**
     * @var MockObject
     */
    private $gripPoolMock;

    /**
     * @var MockObject
     */
    private $attributeMock;

    /**
     * @var MockObject
     */
    private $globalConfigMock;

    protected function setUp(): void
    {
        $this->gripPoolMock = $this->createMock(GridPool::class);
        $this->attributeMock = $this->createMock(Attribute::class);
        $this->globalConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->model = new AddressUpdate(
            $this->gripPoolMock,
            $this->attributeMock,
            $this->globalConfigMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAfterProcess($asyncReindexEnabled, $expectedReindexCalledCount)
    {
        $billingId = 100;
        $shippingId = 200;
        $orderId = 50;

        $orderMock = $this->createPartialMock(
            Order::class,
            ['hasInvoices', 'getBillingAddress', 'getShippingAddress', 'getInvoiceCollection', 'getId']
        );

        $shippingMock = $this->createMock(Address::class);
        $shippingMock->expects($this->once())->method('getId')->willReturn($shippingId);

        $billingMock = $this->createMock(Address::class);
        $billingMock->expects($this->once())->method('getId')->willReturn($billingId);

        $invoiceCollectionMock = $this->createMock(Collection::class);
        $invoiceMock = $this->createMock(Invoice::class);
        $invoiceCollectionMock->expects($this->once())->method('getItems')->willReturn([$invoiceMock]);

        $orderMock->expects($this->once())->method('hasInvoices')->willReturn(true);
        $orderMock->expects($this->once())->method('getBillingAddress')->willReturn($billingMock);
        $orderMock->expects($this->once())->method('getShippingAddress')->willReturn($shippingMock);
        $orderMock->expects($this->once())->method('getInvoiceCollection')->willReturn($invoiceCollectionMock);
        $orderMock->expects($this->exactly($expectedReindexCalledCount))
            ->method('getId')
            ->willReturn($orderId);

        $invoiceMock->expects($this->once())->method('getBillingAddressId')->willReturn(null);
        $invoiceMock->expects($this->once())->method('getShippingAddressId')->willReturn(null);
        $invoiceMock->expects($this->once())->method('setShippingAddressId')->with($shippingId)->willReturnSelf();
        $invoiceMock->expects($this->once())->method('setBillingAddressId')->with($billingId)->willReturnSelf();

        $this->attributeMock->expects($this->once())
            ->method('saveAttribute')
            ->with($invoiceMock, ['billing_address_id', 'shipping_address_id'])
            ->willReturnSelf();

        $this->gripPoolMock->expects($this->exactly($expectedReindexCalledCount))
            ->method('refreshByOrderId')
            ->with($orderId)
            ->willReturnSelf();

        $this->globalConfigMock->expects($this->once())
            ->method('getValue')
            ->with('dev/grid/async_indexing')
            ->willReturn($asyncReindexEnabled);

        $this->model->afterProcess(
            $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Handler\Address::class),
            $this->createMock(\Magento\Sales\Model\ResourceModel\Order\Handler\Address::class),
            $orderMock
        );
    }

    public function dataProvider()
    {
        return [
            'Do not reindex when async is enabled' => [
                true,
                0
            ],
            'Reindex when async is disabled' => [
                false,
                1
            ],
        ];
    }
}
