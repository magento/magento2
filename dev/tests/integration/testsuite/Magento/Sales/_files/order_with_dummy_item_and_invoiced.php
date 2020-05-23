<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');
/** @var Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

$orderItems = [
    [
        \Magento\Sales\Api\Data\OrderItemInterface::PRODUCT_ID   => 2,
        \Magento\Sales\Api\Data\OrderItemInterface::BASE_PRICE   => 100,
        \Magento\Sales\Api\Data\OrderItemInterface::ORDER_ID     => $order->getId(),
        \Magento\Sales\Api\Data\OrderItemInterface::QTY_ORDERED  => 2,
        \Magento\Sales\Api\Data\OrderItemInterface::QTY_INVOICED => 2,
        \Magento\Sales\Api\Data\OrderItemInterface::PRICE        => 100,
        \Magento\Sales\Api\Data\OrderItemInterface::ROW_TOTAL    => 102,
        \Magento\Sales\Api\Data\OrderItemInterface::PRODUCT_TYPE => 'bundle',
        'children'                                               => [
            [
                \Magento\Sales\Api\Data\OrderItemInterface::PRODUCT_ID   => 13,
                \Magento\Sales\Api\Data\OrderItemInterface::ORDER_ID     => $order->getId(),
                \Magento\Sales\Api\Data\OrderItemInterface::QTY_ORDERED  => 2,
                \Magento\Sales\Api\Data\OrderItemInterface::QTY_INVOICED => 2,
                \Magento\Sales\Api\Data\OrderItemInterface::BASE_PRICE   => 90,
                \Magento\Sales\Api\Data\OrderItemInterface::PRICE        => 90,
                \Magento\Sales\Api\Data\OrderItemInterface::ROW_TOTAL    => 92,
                \Magento\Sales\Api\Data\OrderItemInterface::PRODUCT_TYPE => 'simple',
                'product_options'                                        => [
                    'bundle_selection_attributes' => '{"qty":2}',
                ],
            ]
        ],
    ]
];

// Invoiced all existing order items.
foreach ($order->getAllItems() as $item) {
    $item->setQtyInvoiced(1);
    $item->save();
}

saveOrderItems($orderItems);

/**
 * Save Order Items.
 *
 * @param array $orderItems
 * @param \Magento\Sales\Model\Order\Item|null $parentOrderItem [optional]
 * @return void
 */
function saveOrderItems(array $orderItems, $parentOrderItem = null)
{
    /** @var array $orderItemData */
    foreach ($orderItems as $orderItemData) {
        /** @var $orderItem \Magento\Sales\Model\Order\Item */
        $orderItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Sales\Model\Order\Item::class
        );
        if (null !== $parentOrderItem) {
            $orderItemData['parent_item'] = $parentOrderItem;
        }
        $orderItem
            ->setData($orderItemData)
            ->save();

        if (isset($orderItemData['children'])) {
            saveOrderItems($orderItemData['children'], $orderItem);
        }
    }
}
