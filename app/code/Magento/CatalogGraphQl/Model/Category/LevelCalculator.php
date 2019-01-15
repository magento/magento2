<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Category;

use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Category;

/**
 * Determine level data for GraphQL Category request
 */
class LevelCalculator
{
    /**
     * @param ResourceConnection $resourceConnection
     * @param Category $resourceCategory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Category $resourceCategory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->resourceCategory = $resourceCategory;
    }

    /**
     * Calculate level data for root category ID specified in GraphQL request
     *
     * @param int $rootCategoryId
     * @return int
     */
    public function calculate(int $rootCategoryId) : int
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName('catalog_category_entity'), 'level')
            ->where($this->resourceCategory->getLinkField() . " = ?", $rootCategoryId);
        return (int) $connection->fetchOne($select);
    }
}
