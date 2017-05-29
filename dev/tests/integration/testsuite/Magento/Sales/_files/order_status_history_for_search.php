<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Sales\Model\Order\Status\History;
use Magento\TestFramework\Helper\Bootstrap;

require 'default_rollback.php';
require __DIR__ . '/order.php';

$comments = [
    [
        'comment' => 'comment 1',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 2',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 3',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 4',
        'is_visible_on_front' => 1,
        'is_customer_notified' => 1,
    ],
    [
        'comment' => 'comment 5',
        'is_visible_on_front' => 0,
        'is_customer_notified' => 1,
    ],
];

foreach ($comments as $data) {
    /** @var $comment History */
    $comment = Bootstrap::getObjectManager()->create(History::class);
    $comment->setParentId($order->getId());
    $comment->setComment($data['comment']);
    $comment->setIsVisibleOnFront($data['is_visible_on_front']);
    $comment->setIsCustomerNotified($data['is_customer_notified']);
    $comment->save();
}
