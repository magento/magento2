<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Braintree\Model\Ui\ConfigProvider;

$objectManager = Bootstrap::getObjectManager();

/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

/** @var $mutableScopeConfig */
$mutableScopeConfig = $objectManager->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
);

$configWriter->delete('payment/' . ConfigProvider::CODE . '/merchant_id');
$configWriter->delete('payment/' . ConfigProvider::CODE . '/public_key');
$configWriter->delete('payment/' . ConfigProvider::CODE . '/private_key');
$configWriter->delete('payment/' . ConfigProvider::CODE . '/active');
$configWriter->delete('payment/' . ConfigProvider::CODE . '/environment');
$configWriter->delete('payment/' . ConfigProvider::CC_VAULT_CODE . '/active');
