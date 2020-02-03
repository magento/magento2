<?php
/**
 * Create store fixture
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var \Magento\Store\Model\Store $store */
$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if (!$store->load('inactive_store', 'code')->getId()) {
    $websiteId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()
        ->getId();
    $groupId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()->getDefaultGroupId();
    $store->setCode(
        'inactive_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Inactive Store'
    )->setSortOrder(
        15
    )->setIsActive(
        0
    );
    $store->save();
}
