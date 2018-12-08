<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

/** @var \Magento\Framework\ObjectManagerInterface  $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Store\Model\StoreManager $store */
$store = $objectManager->get(\Magento\Store\Model\StoreManager::class);

/** @var $customer \Magento\Customer\Model\Customer*/
$customer = $objectManager->create(\Magento\Customer\Model\Customer::class);
$customer->setWebsiteId($store->getDefaultStoreView()->getWebsiteId());
$customer->loadByEmail('john.doe@magento.com');
if ($customer->getId()) {
    $customer->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
