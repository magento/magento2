<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
$websiteId = $website->load('secondwebsite', 'code')->getId();
if ($websiteId) {
    $website->delete();
}
$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if ($store->load('secondstore', 'code')->getId()) {
    $store->delete();
}

/** @var $website \Magento\Store\Model\Website */
$websiteId = $website->load('thirdwebsite', 'code')->getId();
if ($websiteId) {
    $website->delete();
}

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if ($store->load('thirdstore', 'code')->getId()) {
    $store->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
