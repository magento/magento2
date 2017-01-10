<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\App\ResourceConnection;

/**
 * Map that holds data for categories used by products found in root category
 */
class DataCategoryUsedInProductsHashMap implements HashMapInterface
{
    /** @var int[] */
    private $hashMap = [];

    /** @var HashMapPool */
    private $hashMapPool;

    /** @var ResourceConnection */
    private $connection;

    /**
     * @param ResourceConnection $connection
     * @param HashMapPool $hashMapPool
     */
    public function __construct(
        ResourceConnection $connection,
        HashMapPool $hashMapPool
    ) {
        $this->connection = $connection;
        $this->hashMapPool = $hashMapPool;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllData($categoryId)
    {
        return $this->generateData($categoryId);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($categoryId, $key)
    {
        $categorySpecificData = $this->generateData($categoryId);
        return $categorySpecificData[$key];
    }

    /**
     * Returns an array of product ids for all DataProductHashMap list,
     * that occur in other categories not part of DataCategoryHashMap list
     *
     * @param int $categoryId
     * @return array
     */
    private function generateData($categoryId)
    {
        if (!isset($this->hashMap[$categoryId])) {
            $productsLinkConnection = $this->connection->getConnection();
            $select = $productsLinkConnection->select()
                ->from($this->connection->getTableName('catalog_category_product'), ['category_id'])
                ->where(
                    $productsLinkConnection->prepareSqlCondition(
                        'product_id',
                        [
                            'in' => $this->hashMapPool->getDataMap(
                                DataProductHashMap::class,
                                $categoryId
                            )->getAllData($categoryId)
                        ]
                    )
                )
                ->where(
                    $productsLinkConnection->prepareSqlCondition(
                        'category_id',
                        [
                            'nin' => $this->hashMapPool->getDataMap(
                                DataCategoryHashMap::class,
                                $categoryId
                            )->getAllData($categoryId)
                        ]
                    )
                )->group('category_id');

            $this->hashMap[$categoryId] = $productsLinkConnection->fetchCol($select);
        }
        return $this->hashMap[$categoryId];
    }

    /**
     * {@inheritdoc}
     */
    public function resetData($categoryId)
    {
        $this->hashMapPool->resetMap(DataProductHashMap::class, $categoryId);
        $this->hashMapPool->resetMap(DataCategoryHashMap::class, $categoryId);
        unset($this->hashMap[$categoryId]);
    }
}
