<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

$websiteCodes = ['eu_website', 'us_website', 'global_website'];

$store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
$store->load('default');
$rootCategoryId = $store->getRootCategoryId();

foreach ($websiteCodes as $key => $websiteCode) {
    /** @var Website $website */
    $website = Bootstrap::getObjectManager()->create(Website::class);
    $website->setData([
        'code' => $websiteCode,
        'name' => 'Test Website ' . $websiteCode,
        'is_default' => '0',
    ]);
    $website->save();

    $store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
    $store->setCode(
        'store_for_' . $websiteCode
    )->setWebsiteId(
        $website->getId()
    )->setName(
        'store_for_' . $websiteCode
    )->setSortOrder(
        10 + $key
    )->setIsActive(
        1
    );

    /** @var GroupInterface $group */
    $group = Bootstrap::getObjectManager()->create(GroupInterface::class);
    $group->setName('store_view_' . $websiteCode);
    $group->setCode('store_view_' . $websiteCode);
    $group->setWebsiteId($website->getId());
    $group->setDefaultStoreId($store->getId());
    $group->setRootCategoryId($rootCategoryId);
    $group->save();

    $website->setDefaultGroupId($group->getId());
    $website->save();
    $store->setGroupId($group->getId());
    $store->save();
}

$objectManager = Bootstrap::getObjectManager();
/* Refresh stores memory cache */
$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
