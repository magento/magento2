<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\App\ResourceConnection;

/**
 * Map that holds data for categories used by products found in root category
 */
class DataCategoryUsedInProductsMap implements DataMapInterface
{
    /** @var array */
    private $data = [];

    /** @var DataMapPoolInterface */
    private $dataMapPool;

    /** @var ResourceConnection */
    private $connection;

    /**
     * @param ResourceConnection $connection
     * @param DataMapPoolInterface $dataMapPool,
     */
    public function __construct(
        ResourceConnection $connection,
        DataMapPoolInterface $dataMapPool
    ) {
        $this->connection = $connection;
        $this->dataMapPool = $dataMapPool;
    }

    /**
     * Gets all data from a map identified by a category Id
     *
     * @param int $categoryId
     * @return array
     */
    public function getData($categoryId)
    {
        if (empty($this->data[$categoryId])) {
            $this->data[$categoryId] = $this->queryData($categoryId);
        }
        return $this->data[$categoryId];
    }

    /**
     * Queries the database and returns results
     *
     * @param int $categoryId
     * @return array
     */
    private function queryData($categoryId)
    {
        $productsLinkConnection = $this->connection->getConnection();
        $select = $productsLinkConnection->select()
            ->from($this->connection->getTableName('catalog_category_product'), ['category_id'])
            ->where(
                $productsLinkConnection->prepareSqlCondition(
                    'product_id',
                    ['in' => $this->dataMapPool->getDataMap(DataProductMap::class, $categoryId)->getData($categoryId)]
                )
            )
            ->where(
                $productsLinkConnection->prepareSqlCondition(
                    'category_id',
                    ['nin' => $this->dataMapPool->getDataMap(DataCategoryMap::class, $categoryId)->getData($categoryId)]
                )
            )
            ->group('category_id');

        return $productsLinkConnection->fetchCol($select);
    }

    /**
     * Resets current map and it's dependencies
     *
     * @param int $categoryId
     * @return $this
     */
    public function resetData($categoryId)
    {
        $this->dataMapPool->resetDataMap(DataProductMap::class, $categoryId);
        $this->dataMapPool->resetDataMap(DataCategoryMap::class, $categoryId);
        unset($this->data);
        $this->data = [];
        return $this;
    }
}
