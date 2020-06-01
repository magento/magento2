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
        for ($storeId = 0; $storeId <= 256; $storeId++) {
            $connection->createTable(
                $connection->createTableByDdl(
                    $this->resourceConnection->getTableName($this->prototype),
                    $this->resourceConnection->getTableName($this->prototype) . '_' . Store::ENTITY . $storeId
                )
            );
        }
    }
}
