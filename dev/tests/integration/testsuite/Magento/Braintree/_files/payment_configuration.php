<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

$processConfigData = function (Config $config, array $data) {
    foreach ($data as $key => $value) {
        $config->setDataByPath($key, $value);
        $config->save();
    }
};

// save payment configuration for the default scope
$configData = [
    'payment/braintree/merchant_id' => 'def_merchant_id',
    'payment/braintree/public_key' => $encryptor->encrypt('def_public_key'),
    'payment/braintree/private_key' => $encryptor->encrypt('def_private_key'),
];
/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
$processConfigData($defConfig, $configData);

// save payment configuration per store
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/store.php');
$storeConfigData = [
    'payment/braintree/merchant_id' => 'store_merchant_id',
    'payment/braintree/public_key' => $encryptor->encrypt('store_public_key'),
];
/** @var Config $storeConfig */
$storeConfig = $objectManager->create(Config::class);
$storeConfig->setScope(ScopeInterface::SCOPE_STORES);
$storeConfig->setStore('test');
$processConfigData($storeConfig, $storeConfigData);

// save payment website config data
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');
$websiteId = $websiteRepository->get('test')->getCode();
$websiteConfigData = [
    'payment/braintree/merchant_id' => 'website_merchant_id',
    'payment/braintree/private_key' => $encryptor->encrypt('website_private_key'),
];
/** @var Config $websiteConfig */
$websiteConfig = $objectManager->create(Config::class);
$websiteConfig->setScope(ScopeInterface::SCOPE_WEBSITES);
$websiteConfig->setWebsite($websiteId);
$processConfigData($websiteConfig, $websiteConfigData);
