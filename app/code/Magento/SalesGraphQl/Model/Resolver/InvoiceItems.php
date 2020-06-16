<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\Data\InvoiceInterface as Invoice;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Resolver\OrderItem\DataProvider as OrderItemProvider;

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
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model']) || !($value['model'] instanceof Invoice)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!isset($value['order']) || !($value['order'] instanceof Order)) {
            throw new LocalizedException(__('"order" value should be specified'));
        }

        /** @var Invoice $invoiceModel */
        $invoiceModel = $value['model'];
        $parentOrder = $value['order'];

        return $this->valueFactory->create(
            $this->getInvoiceItems($parentOrder, $invoiceModel->getItems())
        );
    }

    /**
     * Get Invoice Item Data
     *
     * @param Order $order
     * @param array $invoiceItems
     * @return \Closure
     */
    public function getInvoiceItems(Order $order, array $invoiceItems)
    {
        $itemsList = [];
        foreach ($invoiceItems as $Item) {
            $this->orderItemProvider->addOrderItemId((int)$Item->getOrderItemId());
        }
        $itemsList = function () use ($order, $invoiceItems, $itemsList) {
            foreach ($invoiceItems as $invoiceItem) {
                $orderItem = $this->orderItemProvider->getOrderItemById((int)$invoiceItem->getOrderItemId());
                /** @var OrderItemInterface $orderItemModel */
                $orderItemModel = $orderItem['model'];
                if (!$orderItemModel->getParentItem()) {
                    $invoiceItemData = $this->getInvoiceItemData($order, $invoiceItem);
                    if (isset($invoiceItemData)) {
                        $itemsList[$invoiceItem->getOrderItemId()] = $invoiceItemData;
                    }
                }
            }
            return $itemsList;
        };
        return $itemsList;
    }

    /**
     * Get resolved Invoice Item Data
     *
     * @param Order $order
     * @param InvoiceItemInterface $invoiceItem
     * @return array
     */
    private function getInvoiceItemData(Order $order, InvoiceItemInterface $invoiceItem)
    {
        /** @var OrderItemInterface $orderItem */
        $orderItem = $this->orderItemProvider->getOrderItemById((int)$invoiceItem->getOrderItemId());
        return [
            'id' => base64_encode($invoiceItem->getEntityId()),
            'product_name' => $invoiceItem->getName(),
            'product_sku' => $invoiceItem->getSku(),
            'product_sale_price' => [
                'value' => $invoiceItem->getPrice(),
                'currency' => $order->getOrderCurrency()
            ],
            'quantity_invoiced' => $invoiceItem->getQty(),
            'model' => $invoiceItem,
            'product_type' => $orderItem['product_type']
        ];
    }
}
