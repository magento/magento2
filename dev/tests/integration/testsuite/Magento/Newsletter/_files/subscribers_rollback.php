<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Collection $collection */
$subscriberCollection =  $objectManager->get(\Magento\Newsletter\Model\ResourceModel\Subscriber\Collection::class);
foreach ($subscriberCollection as $subscriber) {
    /** @var Magento\Newsletter\Model\Subscriber $subscriber */
    $subscriber->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../../Magento/Customer/_files/customer_rollback.php';
require __DIR__ . '/../../../Magento/Store/_files/core_fixturestore_rollback.php';
