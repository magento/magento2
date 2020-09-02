<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$storeId = $objectManager->get(StoreManagerInterface::class)
    ->getStore()
    ->getId();

/** @var Subscriber $subscriber */
$subscriber = $objectManager->get(Subscriber::class);
$subscriber->loadBySubscriberEmail('guest@example.com', (int)$storeId);
if ($subscriber->getId()) {
    $subscriber->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
