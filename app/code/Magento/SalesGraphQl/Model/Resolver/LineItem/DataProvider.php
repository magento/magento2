<?php

namespace Magento\SalesGraphQl\Model\Resolver\LineItem;

use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Sales\Api\Data\LineItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\SalesGraphQl\Model\Resolver\OrderItem\DataProvider as OrderItemProvider;

class DataProvider
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
    public function __construct(ValueFactory $valueFactory, OrderItemProvider $orderItemProvider)
    {
        $this->valueFactory = $valueFactory;
        $this->orderItemProvider = $orderItemProvider;
    }

    /**
     * Resolves Line Items (Invoice Items, Shipment Items)
     *
     * @param Order $order
     * @param array $lineItems
     * @return \Closure
     */
    public function getLineItems(Order $order, array $lineItems)
    {
        $itemsList = [];
        $lineItemToOrderMap = [];
        foreach ($lineItems as $lineItem) {
            $lineItemToOrderMap[$lineItem->getOrderItemId()] = $lineItem;
            $this->orderItemProvider->addOrderItemId($lineItem->getOrderItemId());
        }
        $itemsList = function () use ($order, $lineItems, $itemsList, $lineItemToOrderMap) {
            foreach ($lineItems as $lineItem) {
                $orderItem = $this->orderItemProvider->getOrderItemById((int)$lineItem->getOrderItemId());
                /** @var OrderItemInterface $orderItemModel */
                $orderItemModel = $orderItem['model'];
                if (!$orderItemModel->getParentItem()) {
                    $lineItemData = $this->getLineItemData($order, $lineItem, $lineItemToOrderMap);
                    if (isset($lineItemData)) {
                        $itemsList[$lineItem->getOrderItemId()] = $lineItemData;
                    }
                }
            }
            return $itemsList;
        };
        return $itemsList;
    }

    /**
     * Get resolved Line Item Data
     *
     * @param Order $order
     * @param LineItemInterface $lineItem
     * @param array|null $lineItemToOrderMap
     * @return array
     */
    private function getLineItemData(Order $order, LineItemInterface $lineItem, $lineItemToOrderMap = null)
    {
        $orderItem = $this->orderItemProvider->getOrderItemById((int)$lineItem->getOrderItemId());
        return [
            'product_name' => $lineItem->getName(),
            'product_sku' => $lineItem->getSku(),
            'product_sale_price' => [
                'value' => $lineItem->getPrice(),
                'currency' => $order->getOrderCurrency()
            ],
            'product_type' => $orderItem['product_type'],
            'quantity_invoiced' => $lineItem->getQty(),
            'model' => $lineItem,
            'line_item_to_order_item_map' => $lineItemToOrderMap,
            'order' => $order,
        ];
    }
}
