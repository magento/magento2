<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$mutableScopeConfig = Bootstrap::getObjectManager()->create(MutableScopeConfigInterface::class);
$mutableScopeConfig->setValue(
    'customer/create_account/confirm',
    $this->confirmationConfigScopeValue,
    ScopeInterface::SCOPE_WEBSITES,
    null
);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
