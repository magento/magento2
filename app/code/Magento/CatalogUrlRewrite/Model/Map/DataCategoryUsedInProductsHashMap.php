<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\App\ResourceConnection;

/**
 * Map that holds data for categories used by products found in root category
 * @since 2.2.0
 */
class DataCategoryUsedInProductsHashMap implements HashMapInterface
{
    /**
     * @var int[]
     * @since 2.2.0
     */
    private $hashMap = [];

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\HashMapPool
     * @since 2.2.0
     */
    private $hashMapPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $connection;

    /**
     * @param ResourceConnection $connection
     * @param HashMapPool $hashMapPool
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $connection,
        HashMapPool $hashMapPool
    ) {
        $this->connection = $connection;
        $this->hashMapPool = $hashMapPool;
    }

    /**
     * Returns an array of product ids for all DataProductHashMap list,
     * that occur in other categories not part of DataCategoryHashMap list
     *
     * @param int $categoryId
     * @return array
     * @since 2.2.0
     */
    public function getAllData($categoryId)
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
     * @since 2.2.0
     */
    public function getData($categoryId, $key)
    {
        $categorySpecificData = $this->getAllData($categoryId);
        if (isset($categorySpecificData[$key])) {
            return $categorySpecificData[$key];
        }
        return [];
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function resetData($categoryId)
    {
        $this->hashMapPool->resetMap(DataProductHashMap::class, $categoryId);
        $this->hashMapPool->resetMap(DataCategoryHashMap::class, $categoryId);
        unset($this->hashMap[$categoryId]);
    }
}
