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

/** @var \Magento\Store\Model\Website $website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->load('test', 'code');

/** @var $quote \Magento\Quote\Model\Quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setSharedStoreIds($website->getStoreIds());
$quote->load('test01', 'reserved_order_id');
if ($quote->getId()) {
    $quote->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../Catalog/_files/products_with_websites_and_stores_rollback.php';
