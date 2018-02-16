<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/queue.php';

$problem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Newsletter\Model\Problem');
// $firstSubscriberId comes from subscribers.php
$problem->setSubscriberId($firstSubscriberId)
    ->setQueueId($queue->getQueueId())
    ->setProblemErrorCode(11)
    ->setProblemErrorText('error text')
    ->save();
