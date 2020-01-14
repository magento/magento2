<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$mutableScopeConfig = $objectManager->create(MutableScopeConfigInterface::class);

$mutableScopeConfig->setValue(
    'customer/create_account/confirm',
    1,
    ScopeInterface::SCOPE_WEBSITES,
    null
);
