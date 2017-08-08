<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ResourceConnection $connection */
$connection = Bootstrap::getObjectManager()->get(ResourceConnection::class);
$connection->getConnection()->delete(
    'inventory_source',
    [
        SourceInterface::NAME . ' IN (?)' => [
            'source-name-1',
            'source-name-2',
            'source-name-3',
            'source-name-4',
        ],
    ]
);
