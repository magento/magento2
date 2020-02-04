<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var $website Website */
$website = $objectManager->create(Website::class);
$website->setData(['code' => 'test_website', 'name' => 'Test Website', 'default_group_id' => '1', 'is_default' => '0']);
$websiteResourceModel = $objectManager->create(WebsiteResourceModel::class);
$websiteResourceModel->save($website);

$websiteId = $website->getId();
$store = $objectManager->create(Store::class);
$groupId = Bootstrap::getObjectManager()->get(StoreManagerInterface::class)
    ->getWebsite()
    ->getDefaultGroupId();
$store->setCode('test_second_store')
    ->setWebsiteId($websiteId)
    ->setGroupId($groupId)
    ->setName('Test Second Store')
    ->setSortOrder(10)
    ->setIsActive(1);
$storeResourceModel = $objectManager->create(StoreResourceModel::class);
$storeResourceModel->save($store);

/* Refresh stores memory cache */
$objectManager->get(StoreManagerInterface::class)->reinitStores();

$processConfigData = function (Config $config, array $data) {
    foreach ($data as $key => $value) {
        $config->setDataByPath($key, $value);
        $config->save();
    }
};

// save signifyd configuration for the default scope
$configData = [
    'fraud_protection/signifyd/active' => '1',
];
/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
$processConfigData($defConfig, $configData);

// save signifyd website config data
$websiteConfigData = [
    'fraud_protection/signifyd/active' => '0',
];
/** @var Config $websiteConfig */
$websiteConfig = $objectManager->create(Config::class);
$websiteConfig->setScope(ScopeInterface::SCOPE_WEBSITES);
$websiteConfig->setWebsite($websiteId);
$processConfigData($websiteConfig, $websiteConfigData);
