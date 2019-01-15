<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Framework\App\ResourceConnection $resource */
$resource = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
$connection = $resource->getConnection();
$tableName = $resource->getTableName('core_config_data');

$connection->query("DELETE FROM $tableName WHERE path = 'design/header/welcome';");
