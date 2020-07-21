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
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Framework\DB\Transaction;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order_with_customer.php');

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
            'product_calculations' => 0,
            'shipment_type' => 0,
            "bundle_options"=> [
                "1" => [
                    "option_id"=> "1",
                    "label" => "Bundle Option 1",
                    "value" => [
                        [
                            "title" => "bundle_simple_1",
                            "qty" => 1,
                            "price" => 10
                        ]
                    ]
                ],
            ],
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
     * @param Order $order
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

$shipmentItems = [];
foreach ($order->getItems() as $orderItem) {
    $shipmentItems[$orderItem->getId()] = $orderItem->getQtyOrdered();
}
$shipment = $objectManager->get(ShipmentFactory::class)->create($order, $shipmentItems);
$shipment->register();

/** @var Transaction $transaction */
$transaction = $objectManager->create(Transaction::class);
$transaction->addObject($shipment)->addObject($order)->save();
