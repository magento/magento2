<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/process_config_data.php';

$objectManager = Bootstrap::getObjectManager();

$configData = [
    'sendfriend/email/max_per_hour',
    'sendfriend/email/check_by'
];
/** @var WriterInterface $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$deleteConfigData($configWriter, $configData, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
