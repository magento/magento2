<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Magento\Framework\App\ResourceConnection $resourceConnection */
$resourceConnection = $objectManager->create(Magento\Framework\App\ResourceConnection::class);

$connection = $resourceConnection->getConnection();
$tableName = $resourceConnection->getTableName('inventory_reservation');

$payload = [
    'stock_id' => 1,
    'sku' => 'simple',
    'quantity' => -5,
    'metadata' => '{"event_type":"shipment_created","object_type":"order","object_id":"1"}'
];

$qry = $connection->insert($tableName, $payload);
