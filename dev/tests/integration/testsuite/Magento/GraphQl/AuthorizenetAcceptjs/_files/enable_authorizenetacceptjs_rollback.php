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
use Magento\AuthorizenetAcceptjs\Gateway\Config;

$objectManager = Bootstrap::getObjectManager();

/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$configWriter->delete('payment/' . Config::METHOD . '/active');
$configWriter->delete('payment/' . Config::METHOD . '/environment');
$configWriter->delete('payment/' . Config::METHOD . '/login');
$configWriter->delete('payment/' . Config::METHOD . '/trans_key');
$configWriter->delete('payment/' . Config::METHOD . '/public_client_key');
$configWriter->delete('payment/' . Config::METHOD . '/trans_signature_key');

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
