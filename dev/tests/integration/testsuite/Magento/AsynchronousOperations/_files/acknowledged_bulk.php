<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/AsynchronousOperations/_files/bulk.php');

$resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
$acknowledgedBulkTable = $resource->getTableName('magento_acknowledged_bulk');
$acknowledgedBulkQuery = "INSERT INTO {$acknowledgedBulkTable} (`bulk_uuid`) VALUES ('bulk-uuid-4'), ('bulk-uuid-5');";
$resource->getConnection()->query($acknowledgedBulkQuery);
