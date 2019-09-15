<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Config $config */
$config = Bootstrap::getObjectManager()->create(Config::class);
$config->deleteConfig('customer/create_account/confirm');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
