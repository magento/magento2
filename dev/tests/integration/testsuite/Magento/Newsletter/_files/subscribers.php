<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Store/_files/core_fixturestore.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer.php';

$currentStore = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore()->getId();
$otherStore = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->getStore(
    'fixturestore'
)->getId();

/** @var \Magento\Newsletter\Model\Subscriber $subscriber */
$subscriber = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Newsletter\Model\Subscriber::class);
$subscriber->setStoreId($currentStore)
    ->setCustomerId(1)
    ->setSubscriberEmail('customer@example.com')
    ->setSubscriberStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED)
    ->save();
$firstSubscriberId = $subscriber->getId();

$subscriber = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Newsletter\Model\Subscriber::class);
$subscriber->setStoreId($otherStore)
    // Intentionally setting ID to 0 instead of 2 to test fallback mechanism in Subscriber model
    ->setCustomerId(0)
    ->setSubscriberEmail('customer_two@example.com')
    ->setSubscriberStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED)
    ->save();

/** @var \Magento\Newsletter\Model\Subscriber $subscriber */
$subscriber = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Newsletter\Model\Subscriber::class);
$subscriber->setStoreId($currentStore)
    ->setCustomerId(1)
    ->setSubscriberEmail('customer_confirm@example.com')
    ->setSubscriberConfirmCode('ysayquyajua23iq29gxwu2eax2qb6gvy')
    ->setSubscriberStatus(\Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED)
    ->save();
