<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$writerInterface = $objectManager->get(
    \Magento\Framework\App\Config\Storage\WriterInterface::class
);

$registry = $objectManager->get(\Magento\Framework\Registry::class);

if (null !== $registry->registry('web_secure_base_url')) {
    $writerInterface->save(
        'web/secure/base_url',
        $registry->registry('web_secure_base_url')
    );
}

if (null !== $registry->registry('web_secure_use_in_adminhtml')) {
    $writerInterface->save(
        'web/secure/use_in_adminhtml',
        $registry->registry('web_secure_use_in_adminhtml')
    );
}

$registry->unregister('web_secure_base_url');
$registry->unregister('web_secure_use_in_adminhtml');
