<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$scopeInterface = $objectManager->get(
    \Magento\Framework\App\Config\ScopeConfigInterface::class
);
$writerInterface = $objectManager->get(
    \Magento\Framework\App\Config\Storage\WriterInterface::class
);

$registry = $objectManager->get(\Magento\Framework\Registry::class);

$webSecureBaseUrl = $scopeInterface->getValue('web/secure/base_url');
$webSecureUseInAdminhtml = $scopeInterface->getValue('web/secure/use_in_adminhtml');

$registry->register('web_secure_base_url', $webSecureBaseUrl);
$registry->register('web_secure_use_in_adminhtml', $webSecureUseInAdminhtml);

$writerInterface->save(
    'web/secure/base_url',
    'https://example.com/'
);
$writerInterface->save(
    'web/secure/use_in_adminhtml',
    '1'
);
