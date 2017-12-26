<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\TestFramework\ObjectManager;

$objectManager = ObjectManager::getInstance();

require 'order.php';
/** @var Order $order */

$orderItems = [
    [
        OrderItemInterface::PRODUCT_ID => 2,
        OrderItemInterface::BASE_PRICE => 100,
        OrderItemInterface::ORDER_ID => $order->getId(),
        OrderItemInterface::QTY_ORDERED => 2,
        OrderItemInterface::QTY_INVOICED => 2,
        OrderItemInterface::PRICE => 100,
        OrderItemInterface::ROW_TOTAL => 102,
        OrderItemInterface::PRODUCT_TYPE => 'bundle',
        'product_options' => [
            'product_calculations' => 0
        ],
        'children' => [
            [
                OrderItemInterface::PRODUCT_ID => 13,
                OrderItemInterface::ORDER_ID => $order->getId(),
                OrderItemInterface::QTY_ORDERED => 10,
                OrderItemInterface::QTY_INVOICED => 10,
                OrderItemInterface::BASE_PRICE => 90,
                OrderItemInterface::PRICE => 90,
                OrderItemInterface::ROW_TOTAL => 92,
                OrderItemInterface::PRODUCT_TYPE => 'simple',
                'product_options' => [
                    'bundle_selection_attributes' => [
                        'qty' => 2
                    ],
                ],
            ]
        ],
    ]
];

/** @var OrderItemRepositoryInterface $itemRepository */
$itemRepository = $objectManager->get(OrderItemRepositoryInterface::class);
// Invoiced all existing order items.
foreach ($order->getAllItems() as $item) {
    $item->setQtyInvoiced($item->getQtyOrdered());
    $itemRepository->save($item);
}

if (!function_exists('saveOrderItems')) {

    /**
     * Save Order Items.
     *
     * @param array $orderItems
     * @param Item|null $parentOrderItem [optional]
     * @return void
     */
    function saveOrderItems(array $orderItems, Order $order, $parentOrderItem = null)
    {
        $objectManager = ObjectManager::getInstance();

        foreach ($orderItems as $orderItemData) {
            /** @var Item $orderItem */
            $orderItem = $objectManager->create(Item::class);
            if (null !== $parentOrderItem) {
                $orderItemData['parent_item'] = $parentOrderItem;
            }
            $orderItem->setData($orderItemData);
            $order->addItem($orderItem);

            if (isset($orderItemData['children'])) {
                saveOrderItems($orderItemData['children'], $order, $orderItem);
            }
        }
    }
}

saveOrderItems($orderItems, $order);
/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->get(OrderRepositoryInterface::class);
$order = $orderRepository->save($order);
