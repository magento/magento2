<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\AuthorizenetAcceptjs\Gateway\Config;

$objectManager = Bootstrap::getObjectManager();
/** @var EncryptorInterface $encryptor */
$encryptor = $objectManager->get(EncryptorInterface::class);

/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$configWriter->save('payment/' . Config::METHOD . '/active', '1');
$configWriter->save('payment/' . Config::METHOD . '/environment', 'sandbox');
$configWriter->save('payment/' . Config::METHOD . '/login', $encryptor->encrypt('def_login'));
$configWriter->save('payment/' . Config::METHOD . '/trans_key', $encryptor->encrypt('def_trans_key'));
$configWriter->save('payment/' . Config::METHOD . '/public_client_key', $encryptor->encrypt('def_public_client_key'));
$configWriter->save(
    'payment/' . Config::METHOD . '/trans_signature_key',
    $encryptor->encrypt('def_trans_signature_key')
);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
