<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

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
    'payment/braintree/merchant_id' => defined('TESTS_BRAINTREE_MERCHANT_ID') ? TESTS_BRAINTREE_MERCHANT_ID : 'def_merchant_id',
    'payment/braintree/public_key' => $encryptor->encrypt(defined('TESTS_BRAINTREE_PUBLIC_KEY') ? TESTS_BRAINTREE_PUBLIC_KEY : 'def_public_key'),
    'payment/braintree/private_key' => $encryptor->encrypt(defined('TESTS_BRAINTREE_PRIVATE_KEY') ? TESTS_BRAINTREE_PRIVATE_KEY : 'def_private_key'),
    'payment/' . ConfigProvider::CODE . '/active' => '1',
    'payment/' . ConfigProvider::CC_VAULT_CODE . '/active' => '1',
    'payment/' . ConfigProvider::CODE . '/environment' => 'sandbox',
];
/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
$processConfigData($defConfig, $configData);
