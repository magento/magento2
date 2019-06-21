<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ResourceConnection $connection */
$connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
$connection->getConnection()->delete(
    $connection->getTableName('inventory_source'),
    [
        SourceInterface::SOURCE_CODE . ' IN (?)' => ['source-1', 'source-2'],
    ]
);
