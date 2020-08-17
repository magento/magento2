<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation;

use Magento\Framework\App\ResourceConnection;

/**
 * Fetch root category id for specified store id
 */
class RootCategoryProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get root category for specified store id
     *
     * @param int $storeId
     * @return int
     */
    public function getRootCategory(int $storeId): int
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['store' => $this->resourceConnection->getTableName('store')],
                []
            )
            ->join(
                ['store_group' => $this->resourceConnection->getTableName('store_group')],
                'store.group_id = store_group.group_id',
                ['root_category_id' => 'store_group.root_category_id']
            )
            ->where('store.store_id = ?', $storeId);

        return (int)$connection->fetchOne($select);
    }
}
