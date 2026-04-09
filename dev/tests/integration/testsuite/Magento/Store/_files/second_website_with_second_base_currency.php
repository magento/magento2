<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$websiteId = $websiteRepository->get('test')->getId();
/** @var \Magento\Config\Model\ResourceModel\Config $configResource */
$configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
$configResource->saveConfig(
    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
    'EUR',
    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
    $websiteId
);
$configResource->saveConfig(
    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
    'EUR,USD',
    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
    $websiteId
);
$configResource->saveConfig(
    \Magento\Catalog\Helper\Data::XML_PATH_PRICE_SCOPE,
    \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE,
    'default',
    0
);

/** @var \Magento\Config\App\Config\Type\System $config */
$config = $objectManager->get(\Magento\Config\App\Config\Type\System::class);
$config->clean();
