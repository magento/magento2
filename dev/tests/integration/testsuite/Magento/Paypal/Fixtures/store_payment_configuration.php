<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

// save payment configuration per store
require __DIR__ . '/process_config_data.php';
require __DIR__ . '/../../Store/_files/store.php';

$objectManager = Bootstrap::getObjectManager();

/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

$storeConfigData = [
    'payment/payflowpro/partner' => 'store_partner',
    'payment/payflowpro/vendor' => 'store_vendor',
    'payment/payflowpro/user' => $encryptor->encrypt('store_user'),
    'payment/payflowpro/pwd' => $encryptor->encrypt('store_pwd'),
];
/** @var Config $storeConfig */
$storeConfig = $objectManager->create(Config::class);
$storeConfig->setScope(ScopeInterface::SCOPE_STORES);
$storeConfig->setStore('test');
$processConfigData($storeConfig, $storeConfigData);
