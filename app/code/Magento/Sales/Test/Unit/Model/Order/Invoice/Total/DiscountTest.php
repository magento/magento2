<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\Discount;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiscountTest extends TestCase
{
    /**
     * @var Discount
     */
    protected $model;

    /**
     * @var Order|MockObject
     */
    protected $order;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * @var Invoice|MockObject
     */
    protected $invoice;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(Discount::class);
        $this->order = $this->createPartialMock(Order::class, [
            'getInvoiceCollection',
        ]);
        $this->invoice = $this->createPartialMock(Invoice::class, [
            'getAllItems',
            'getOrder',
            'roundPrice',
            'isLast',
            'getGrandTotal',
            'getBaseGrandTotal',
            'setGrandTotal',
            'setBaseGrandTotal'
        ]);
    }

    /**
     * Test for collect invoice
     *
     * @param array $invoiceData
     * @dataProvider collectInvoiceData
     * @return void
     */
    public function testCollectInvoiceWithZeroGrandTotal(array $invoiceData): void
    {
        //Set up invoice mock
        /** @var InvoiceItem[] $invoiceItems */
        $invoiceItems = [];
        foreach ($invoiceData as $invoiceItemData) {
            $invoiceItems[] = $this->getInvoiceItem($invoiceItemData);
        }
        $this->invoice->method('getOrder')
            ->willReturn($this->order);
        $this->order->method('getInvoiceCollection')
            ->willReturn([]);
        $this->invoice->method('getAllItems')
            ->willReturn($invoiceItems);
        $this->invoice->method('getGrandTotal')
            ->willReturn(15.6801);
        $this->invoice->method('getBaseGrandTotal')
            ->willReturn(15.6801);

        $this->invoice->expects($this->exactly(1))
            ->method('setGrandTotal')
            ->with(0);
        $this->invoice->expects($this->exactly(1))
            ->method('setBaseGrandTotal')
            ->with(0);
        $this->model->collect($this->invoice);
    }

    /**
     * @return array
     */
    public function collectInvoiceData(): array
    {
        return [
            [
               [
                    [
                        'order_item' => [
                            'qty_ordered' => 1,
                            'discount_amount' => 5.34,
                            'base_discount_amount' => 5.34,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                    [
                        'order_item' => [
                            'qty_ordered' => 1,
                            'discount_amount' => 10.34,
                            'base_discount_amount' => 10.34,
                        ],
                        'is_last' => true,
                        'qty' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get InvoiceItem
     *
     * @param $invoiceItemData array
     * @return InvoiceItem|MockObject
     */
    protected function getInvoiceItem($invoiceItemData)
    {
        /** @var OrderItem|MockObject $orderItem */
        $orderItem = $this->createPartialMock(OrderItem::class, [
            'isDummy',
        ]);
        foreach ($invoiceItemData['order_item'] as $key => $value) {
            $orderItem->setData($key, $value);
        }
        /** @var InvoiceItem|MockObject $invoiceItem */
        $invoiceItem = $this->createPartialMock(InvoiceItem::class, [
            'getOrderItem',
            'isLast',
        ]);
        $invoiceItem->method('getOrderItem')
            ->willReturn($orderItem);
        $invoiceItem->method('isLast')
            ->willReturn($invoiceItemData['is_last']);
        $invoiceItem->getData('qty', $invoiceItemData['qty']);
        return $invoiceItem;
    }
}
