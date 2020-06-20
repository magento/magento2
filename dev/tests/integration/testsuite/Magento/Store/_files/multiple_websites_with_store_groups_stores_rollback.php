<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** Delete the second website **/
$website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
$websiteId = $website->load('second', 'code')->getId();
if ($websiteId) {
    $website->delete();
}
$website2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
$websiteId2 = $website2->load('third', 'code')->getId();
if ($websiteId2) {
    $website2->delete();
}

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if ($store->load('second_store_view', 'code')->getId()) {
    $store->delete();
}

$store2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if ($store2->load('third_store_view', 'code')->getId()) {
    $store2->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
