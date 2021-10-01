<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver\Invoice;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\SalesGraphQl\Model\OrderItem\DataProvider as OrderItemProvider;

/**
 * Resolver for Invoice Items
 */
class InvoiceItems implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var OrderItemProvider
     */
    private $orderItemProvider;

    /**
     * @param ValueFactory $valueFactory
     * @param OrderItemProvider $orderItemProvider
     */
    public function __construct(
        ValueFactory $valueFactory,
        OrderItemProvider $orderItemProvider
    ) {
        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof InvoiceInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!(($value['order'] ?? null) instanceof OrderInterface)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var InvoiceInterface $invoiceModel */
        $invoiceModel = $value['model'];
        /** @var OrderInterface $parentOrderModel */
        $parentOrderModel = $value['order'];

        return $this->valueFactory->create(
            $this->getInvoiceItems($parentOrderModel, $invoiceModel->getItems())
        );
    }

    /**
     * Get invoice items data as promise
     *
     * @param OrderInterface $order
     * @param array $invoiceItems
     * @return \Closure
     */
    public function getInvoiceItems(OrderInterface $order, array $invoiceItems): \Closure
    {
        $itemsList = [];
        foreach ($invoiceItems as $Item) {
            $this->orderItemProvider->addOrderItemId((int)$Item->getOrderItemId());
        }
        return function () use ($order, $invoiceItems, $itemsList): array {
            foreach ($invoiceItems as $invoiceItem) {
                $orderItem = $this->orderItemProvider->getOrderItemById((int)$invoiceItem->getOrderItemId());
                /** @var OrderItemInterface $orderItemModel */
                $orderItemModel = $orderItem['model'];
                if (!$orderItemModel->getParentItem()) {
                    $invoiceItemData = $this->getInvoiceItemData($order, $invoiceItem);
                    if (!empty($invoiceItemData)) {
                        $itemsList[$invoiceItem->getOrderItemId()] = $invoiceItemData;
                    }
                }
            }
            return $itemsList;
        };
    }

    /**
     * Get formatted invoice item data
     *
     * @param OrderInterface $order
     * @param InvoiceItemInterface $invoiceItem
     * @return array
     */
    private function getInvoiceItemData(OrderInterface $order, InvoiceItemInterface $invoiceItem): array
    {
        $orderItem = $this->orderItemProvider->getOrderItemById((int)$invoiceItem->getOrderItemId());
        return [
            'id' => base64_encode($invoiceItem->getEntityId()),
            'product_name' => $invoiceItem->getName(),
            'product_sku' => $invoiceItem->getSku(),
            'product_sale_price' => [
                'value' => $invoiceItem->getPrice(),
                'currency' => $order->getOrderCurrencyCode()
            ],
            'quantity_invoiced' => $invoiceItem->getQty(),
            'model' => $invoiceItem,
            'product_type' => $orderItem['product_type'],
            'order_item' => $orderItem,
            'discounts' => $this->formatDiscountDetails($order, $invoiceItem)
        ];
    }

    /**
     * Returns formatted information about an applied discount
     *
     * @param OrderInterface $associatedOrder
     * @param InvoiceItemInterface $invoiceItem
     * @return array
     */
    private function formatDiscountDetails(OrderInterface $associatedOrder, InvoiceItemInterface $invoiceItem) : array
    {
        if ($associatedOrder->getDiscountDescription() === null
            && $invoiceItem->getDiscountAmount() == 0
            && $associatedOrder->getDiscountAmount() == 0
        ) {
            $discounts = [];
        } else {
            $discounts[] = [
                'label' => $associatedOrder->getDiscountDescription() ?? __('Discount'),
                'amount' => [
                    'value' => abs((float) $invoiceItem->getDiscountAmount()),
                    'currency' => $associatedOrder->getOrderCurrencyCode()
                ]
            ];
        }
        return $discounts;
    }
}
