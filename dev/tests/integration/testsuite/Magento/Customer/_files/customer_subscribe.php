<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Customer\Model\CustomerRegistry;

$resourceConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                    ->create(\Magento\Config\Model\ResourceModel\Config::class);

$resourceConfig->saveConfig(
    'newsletter/general/active',
    false,
    'default',
    0
);
