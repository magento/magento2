<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config\Factory;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Factory $configFactory */
$configFactory = Bootstrap::getObjectManager()->get(Factory::class);
$config = $configFactory->create();
$config->setScope('stores');
$config->setDataByPath('catalog/search/elasticsearch7_server_port', 2309);
$config->save();
