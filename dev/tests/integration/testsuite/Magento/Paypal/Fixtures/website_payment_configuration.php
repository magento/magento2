<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Config\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

// save payment website config data
require __DIR__ . '/../../Store/_files/second_website_with_two_stores.php';
require __DIR__ . '/process_config_data.php';

$objectManager = Bootstrap::getObjectManager();

/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);
$websiteConfigData = [
    'payment/payflowpro/partner' => 'website_partner',
    'payment/payflowpro/vendor' => 'website_vendor',
    'payment/payflowpro/user' => $encryptor->encrypt('website_user'),
    'payment/payflowpro/pwd' => $encryptor->encrypt('website_pwd'),
];
/** @var Config $websiteConfig */
$websiteConfig = $objectManager->create(Config::class);
$websiteConfig->setScope(ScopeInterface::SCOPE_WEBSITES);
$websiteConfig->setWebsite($websiteId);
$processConfigData($websiteConfig, $websiteConfigData);
