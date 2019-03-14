<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ResourceConnection $connection */
$connection = $objectManager->get(ResourceConnection::class);
$tableName = $connection->getTableName('catalog_product_index_eav');
$connection->getConnection()->delete($tableName);
