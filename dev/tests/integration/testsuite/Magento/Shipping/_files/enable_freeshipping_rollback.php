<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$bootstrap = \Magento\TestFramework\Helper\Bootstrap::getInstance();
$bootstrap->loadArea('adminhtml');
$objectManager = $bootstrap::getObjectManager();

$objectManager->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
)->setValue('carriers/freeshipping/active', 0, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
