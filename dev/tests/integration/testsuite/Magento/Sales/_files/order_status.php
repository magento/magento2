<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/order.php';

$orderStatus = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Status::class
);

$data = [
    'status' => 'example',
    'label' => 'Example',
    'store_labels' => [
        1 => 'Store view example',
    ]
];

$orderStatus->setData($data)->setStatus('example');
$orderStatus->save();

$order->setStatus('example');
$order->save();
