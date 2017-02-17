<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager =  \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Config\Model\ResourceModel\Config $configResource */
$configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
$configResource->deleteConfig(
    \Magento\Catalog\Helper\Data::XML_PATH_PRICE_SCOPE,
    'default',
    0
);
$website = $objectManager->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
$websiteId = $website->load('test', 'code')->getId();
if ($websiteId) {
    $configResource->deleteConfig(
        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
        \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
        $websiteId
    );
    $configResource->deleteConfig(
        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
        \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
        $websiteId
    );
}

require 'second_website_with_two_stores_rollback.php';
