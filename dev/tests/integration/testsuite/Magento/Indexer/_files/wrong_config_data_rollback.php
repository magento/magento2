<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ResourceConnection $resource */
$resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
$connection = $resource->getConnection();
$tableName = $resource->getTableName('core_config_data');

$configFactory = Bootstrap::getObjectManager()->get(Factory::class);
$config = $configFactory->create();
$config->setScope('stores');

$engine = $config->getConfigDataValue('catalog/search/engine');
$portField = "catalog/search/{$engine}_server_port";

$connection->query("DELETE FROM $tableName WHERE path = '{$portField}'"
    ." AND scope = 'stores';");
