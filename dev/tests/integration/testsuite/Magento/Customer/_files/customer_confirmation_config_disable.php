<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$mutableScopeConfig = $objectManager->create(MutableScopeConfigInterface::class);

$mutableScopeConfig->setValue(
    'customer/create_account/confirm',
    0,
    ScopeInterface::SCOPE_WEBSITES,
    null
);
