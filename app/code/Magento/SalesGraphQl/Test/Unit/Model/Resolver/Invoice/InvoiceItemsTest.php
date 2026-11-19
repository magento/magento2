<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Test\Unit\Model\Resolver\Invoice;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as OrderItemProvider;
use Magento\SalesGraphQl\Model\Resolver\Invoice\InvoiceItems;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InvoiceItemsTest extends TestCase
{
    /**
     * @var ValueFactory|MockObject
     */
    private $valueFactory;

    /**
     * @var OrderItemProvider|MockObject
     */
    private $orderItemProvider;

    /**
     * @var InvoiceItems
     */
    private $invoiceItemsResolver;

    protected function setUp(): void
    {
        $this->valueFactory = $this->createMock(ValueFactory::class);
        $this->orderItemProvider = $this->createMock(OrderItemProvider::class);
        $this->invoiceItemsResolver = new InvoiceItems(
            $this->valueFactory,
            $this->orderItemProvider
        );
    }

    public function testResolveCastsIntegerInvoiceItemIdBeforeEncoding(): void
    {
        $orderItemId = 7;
        $invoiceItemEntityId = 42;
        $callback = null;

        $field = $this->createMock(Field::class);
        $context = null;
        $info = $this->createMock(ResolveInfo::class);
        $order = $this->createMock(OrderInterface::class);
        $invoice = $this->createMock(InvoiceInterface::class);
        $invoiceItem = $this->createMock(InvoiceItemInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $invoice->expects($this->once())
            ->method('getItems')
            ->willReturn([$invoiceItem]);

        $invoiceItem->method('getOrderItemId')
            ->willReturn($orderItemId);
        $invoiceItem->expects($this->once())
            ->method('getEntityId')
            ->willReturn($invoiceItemEntityId);
        $invoiceItem->method('getName')
            ->willReturn('Test Product');
        $invoiceItem->method('getSku')
            ->willReturn('test-product');
        $invoiceItem->method('getPrice')
            ->willReturn(10.00);
        $invoiceItem->method('getQty')
            ->willReturn(1.00);
        $invoiceItem->method('getDiscountAmount')
            ->willReturn(0.00);

        $order->method('getOrderCurrencyCode')
            ->willReturn('USD');
        $order->method('getDiscountDescription')
            ->willReturn(null);
        $order->method('getDiscountAmount')
            ->willReturn(0.00);

        $orderItem->expects($this->once())
            ->method('getParentItem')
            ->willReturn(null);

        $orderItemData = [
            'model' => $orderItem,
            'product_type' => 'simple'
        ];
        $this->orderItemProvider->expects($this->once())
            ->method('addOrderItemId')
            ->with($orderItemId);
        $this->orderItemProvider->expects($this->exactly(2))
            ->method('getOrderItemById')
            ->with($orderItemId)
            ->willReturn($orderItemData);

        $this->valueFactory->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (\Closure $resolver) use (&$callback): string {
                $callback = $resolver;
                return 'deferred-result';
            });

        $result = $this->invoiceItemsResolver->resolve(
            $field,
            $context,
            $info,
            [
                'model' => $invoice,
                'order' => $order
            ]
        );

        $this->assertSame('deferred-result', $result);
        $this->assertInstanceOf(\Closure::class, $callback);
        $resolvedItems = $callback();

        $this->assertSame(base64_encode('42'), $resolvedItems[$orderItemId]['id']);
    }
}
