<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\MysqlMq\Model\MessageFactory $messageFactory */
$messageFactory = $objectManager->create(\Magento\MysqlMq\Model\MessageFactory::class);
$message1 = $messageFactory->create()
    ->load('topic.updated.use.just.in.tests', 'topic_name');

$messageId1 = $message1->getId();

/** @var \Magento\MysqlMq\Model\MessageStatusFactory $messageStatusFactory */
$queueFactory = $objectManager->create(\Magento\MysqlMq\Model\QueueFactory::class);
$queueId4 = $queueFactory->create()
    ->load('queue4', Magento\MysqlMq\Model\Queue::KEY_NAME)
    ->getId();

$plan = [
    [$messageId1, $queueId4, time(), Magento\MysqlMq\Model\QueueManagement::MESSAGE_STATUS_COMPLETE],
];

/** @var \Magento\MysqlMq\Model\MessageStatusFactory $messageStatusFactory */
$messageStatusFactory = $objectManager->create(\Magento\MysqlMq\Model\MessageStatusFactory::class);
foreach ($plan as $instruction) {
    $messageStatus = $messageStatusFactory->create();

    $messageStatus->setQueueId($instruction[1])
        ->setMessageId($instruction[0])
        ->setUpdatedAt($instruction[2])
        ->setStatus($instruction[3])
        ->save();
}
