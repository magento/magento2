<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\ObjectManager;
use Magento\Newsletter\Model\Problem;
use Magento\Newsletter\Model\QueueFactory;
use Magento\Newsletter\Model\ResourceModel\Queue;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Newsletter/_files/queue.php');

$objectManager = ObjectManager::getInstance();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var Queue $queueResource */
$queueResource = $objectManager->get(Queue::class);
/** @var Subscriber $subscriber */
$subscriber = $objectManager->get(SubscriberFactory::class)->create();
$queue = $objectManager->get(QueueFactory::class)->create();
$queueResource->load($queue, 'support@example.com', 'newsletter_sender_email');
$subscriber->loadByCustomer(1, $storeManager->getStore()->getWebsiteId());

$problem = $objectManager->create(Problem::class);
$problem->setSubscriberId($subscriber->getId())
    ->setQueueId($queue->getQueueId())
    ->setProblemErrorCode(11)
    ->setProblemErrorText('error text')
    ->save();
