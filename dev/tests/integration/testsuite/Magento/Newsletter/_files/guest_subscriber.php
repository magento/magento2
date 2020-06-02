<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$storeId = $objectManager->get(StoreManagerInterface::class)
    ->getStore()
    ->getId();

/** @var Subscriber $subscriber */
$subscriber = $objectManager->create(Subscriber::class);

$subscriber->setStoreId($storeId)
    ->setSubscriberEmail('guest@example.com')
    ->setSubscriberStatus(Subscriber::STATUS_SUBSCRIBED)
    ->save();
