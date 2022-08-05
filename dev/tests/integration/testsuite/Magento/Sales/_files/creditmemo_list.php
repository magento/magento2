<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Sales/_files/order.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->get(OrderInterfaceFactory::class)->create()->loadByIncrementId('100000001');

$creditMemos = [
    [
        'store_id' => 1,
        'grand_total' =>  280.00,
        'order_id' => $order->getId(),
        'email_sent' => 0,
        'send_email' => 0,
        'increment_id' => '123',
        'creditmemo_status' => 1,
        'state'     => 1
    ],
    [
        'store_id' => 1,
        'grand_total' =>  450.00,
        'order_id' => $order->getId(),
        'email_sent' => 1,
        'send_email' => 1,
        'increment_id' => '456',
        'creditmemo_status' => 1,
        'state'     => 1
    ],
    [
        'store_id' => 1,
        'grand_total' =>  10.00,
        'order_id' => $order->getId(),
        'email_sent' => 1,
        'send_email' => 1,
        'increment_id' => '789',
        'creditmemo_status' => 0,
        'state'     => 1
    ],
    [
        'store_id' => 1,
        'grand_total' =>  1110.00,
        'order_id' => $order->getId(),
        'email_sent' => 1,
        'increment_id' => '012',
        'send_email' => 1,
        'creditmemo_status' => 1,
        'state'     => 0
    ],
];

/** @var array $creditMemoData */
foreach ($creditMemos as $creditMemoData) {
    /** @var \Magento\Sales\Model\Order\Creditmemo $creditMemo */
    $creditMemo = $objectManager->create(\Magento\Sales\Model\Order\Creditmemo::class);
    $creditMemo
        ->setData($creditMemoData)
        ->save();
}
