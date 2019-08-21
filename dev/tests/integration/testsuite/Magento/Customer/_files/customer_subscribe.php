<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$resourceConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->create(\Magento\Config\Model\ResourceModel\Config::class);

$resourceConfig->saveConfig(
    'newsletter/general/active',
    false,
    'default',
    0
);
