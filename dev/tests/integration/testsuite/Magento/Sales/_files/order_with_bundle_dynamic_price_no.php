<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = ObjectManager::getInstance();
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

$orderItems = [
    [
        OrderItemInterface::SKU => 'bundle_1',
        OrderItemInterface::NAME => 'bundle_1',
        OrderItemInterface::PRODUCT_ID => 2,
        OrderItemInterface::BASE_PRICE => 100,
        OrderItemInterface::ORDER_ID => $order->getId(),
        OrderItemInterface::QTY_ORDERED => 2,
        OrderItemInterface::PRICE => 100,
        OrderItemInterface::ROW_TOTAL => 102,
        OrderItemInterface::PRODUCT_TYPE => 'bundle',
        'product_options' => [
            'product_calculations' => 1,
            'info_buyRequest' => [
                'bundle_option' => [1 => 1],
                'bundle_option_qty' => 1,
            ]
        ],
        'children' => [
            [
                OrderItemInterface::SKU => 'bundle_simple_1',
                OrderItemInterface::NAME => 'bundle_simple_1',
                OrderItemInterface::PRODUCT_ID => 13,
                OrderItemInterface::ORDER_ID => $order->getId(),
                OrderItemInterface::QTY_ORDERED => 10,
                OrderItemInterface::BASE_PRICE => 90,
                OrderItemInterface::PRICE => 90,
                OrderItemInterface::ROW_TOTAL => 92,
                OrderItemInterface::PRODUCT_TYPE => 'simple',
                'product_options' => [
                    'bundle_selection_attributes' => '{"qty":5}',
                ],
            ],
        ],
    ],
];

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
