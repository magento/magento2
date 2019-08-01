<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/process_config_data.php';

$objectManager = Bootstrap::getObjectManager();

/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

// save payment configuration for the default scope
$configData = [
    'payment/payflowpro/partner' => 'def_partner',
    'payment/payflowpro/vendor' => 'def_vendor',
    'payment/payflowpro/user' => $encryptor->encrypt('def_user'),
    'payment/payflowpro/pwd' => $encryptor->encrypt('def_pwd'),
];
/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
$processConfigData($defConfig, $configData);
