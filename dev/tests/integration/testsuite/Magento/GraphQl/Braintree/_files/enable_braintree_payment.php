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
use Magento\Framework\App\Config\ScopeConfigInterface;

$objectManager = Bootstrap::getObjectManager();

/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

/** @var $mutableScopeConfig */
$mutableScopeConfig = $objectManager->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
);

$configWriter->save('payment/' . ConfigProvider::CODE . '/merchant_id', 'def_merchant_id');
$configWriter->save('payment/' . ConfigProvider::CODE . '/public_key', $encryptor->encrypt('def_public_key'));
$configWriter->save('payment/' . ConfigProvider::CODE . '/private_key', $encryptor->encrypt('def_private_key'));
$configWriter->save('payment/' . ConfigProvider::CODE . '/active', '1');
$configWriter->save('payment/' . ConfigProvider::CODE . '/environment', 'sandbox');
$configWriter->save('payment/' . ConfigProvider::CC_VAULT_CODE . '/active', '1');

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
