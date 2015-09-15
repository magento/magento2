<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
foreach (['queue1', 'queue2', 'queue3', 'queue4', 'demo-queue-1', 'demo-queue-2', 'demo-queue-3', 'demo-queue-4'] as $queueName) {
    /** @var \Magento\MysqlMq\Model\Queue $queue */
    $queue = $objectManager->create('Magento\MysqlMq\Model\Queue');
    try {
        $queue->setName($queueName)->save();
    } catch (Exception $e) {
        /** In case if queue was already added */
    }
}
