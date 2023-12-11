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
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

// save payment configuration per store
Resolver::getInstance()->requireDataFixture('Magento/Store/_files/store.php');

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
foreach ($storeConfigData as $key => $value) {
    $storeConfig->setDataByPath($key, $value);
    $storeConfig->save();
}
