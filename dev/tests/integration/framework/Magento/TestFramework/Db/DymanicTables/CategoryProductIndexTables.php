<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Db\DymanicTables;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

class CategoryProductIndexTables
{
    /**
     * @var int[]
     */
    private $storeIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

    /**
     * @var string
     */
    private $prototype = 'catalog_category_product_index';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Creates category product index tables
     */
    public function createTables(): void
    {
        $connection = $this->resourceConnection->getConnection();
        foreach ($this->storeIds as $storeId) {
            $connection->createTable(
                $connection->createTableByDdl(
                    $this->resourceConnection->getTableName($this->prototype),
                    $this->resourceConnection->getTableName($this->prototype) . '_' . Store::ENTITY . $storeId
                )
            );
        }
    }
}
