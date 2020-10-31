<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;

/**
 * Get all categories where product is visible
 */
class ProductCategories
{
    /**
     * @var IndexScopeResolver
     */
    private $tableResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @param IndexScopeResolver $tableResolver
     * @param ResourceConnection $resourceConnection
     * @param DimensionFactory $dimensionFactory
     */
    public function __construct(
        IndexScopeResolver $tableResolver,
        ResourceConnection $resourceConnection,
        DimensionFactory $dimensionFactory
    ) {
        $this->tableResolver = $tableResolver;
        $this->resourceConnection = $resourceConnection;
        $this->dimensionFactory = $dimensionFactory;
    }

    /**
     * Get category ids for product
     *
     * @param int $productId
     * @param int $storeId
     * @return array
     */
    public function getCategoryIdsByProduct(int $productId, int $storeId)
    {
        $connection = $this->resourceConnection->getConnection();
        $categoryProductTable = $this->getCatalogCategoryProductTableName($storeId);
        $storeTable = $this->resourceConnection->getTableName(Store::ENTITY);
        $storeGroupTable = $this->resourceConnection->getTableName(Group::ENTITY);

        $select = $connection->select()
            ->from(['cat_index' => $categoryProductTable], ['category_id'])
            ->joinInner(['store' => $storeTable], $connection->quoteInto('store.store_id = ?', $storeId), [])
            ->joinInner(
                ['store_group' => $storeGroupTable],
                'store.group_id = store_group.group_id AND cat_index.category_id != store_group.root_category_id',
                []
            )
            ->where('product_id = ?', $productId);

        $categoryIds = $connection->fetchCol($select);

        return $categoryIds;
    }

    /**
     * Get catalog_category_product table name
     *
     * @param int $storeId
     * @return string
     */
    private function getCatalogCategoryProductTableName(int $storeId)
    {
        $dimension = $this->dimensionFactory->create(Store::ENTITY, (string)$storeId);
        $tableName = $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [$dimension]
        );

        return $tableName;
    }
}
