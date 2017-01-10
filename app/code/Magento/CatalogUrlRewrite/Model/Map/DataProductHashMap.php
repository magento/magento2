<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Map that holds data for products ids from a category and subcategories
 */
class DataProductHashMap implements HashMapInterface
{
    /** @var int[] */
    private $hashMap = [];

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var HashMapPool */
    private $hashMapPool;

    /** @var ResourceConnection */
    private $connection;

    /**
     * @param CollectionFactory $collectionFactory
     * @param HashMapPool $hashMapPool
     * @param ResourceConnection $connection
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        HashMapPool $hashMapPool,
        ResourceConnection $connection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->hashMapPool = $hashMapPool;
        $this->connection = $connection;
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
     * Returns an array of ids of all visible products and assigned to a category and all its subcategories
     *
     * @param int $categoryId
     * @return array
     */
    private function generateData($categoryId)
    {
        if (!isset($this->hashMap[$categoryId])) {
            $productsCollection = $this->collectionFactory->create();
            $productsCollection->getSelect()
                ->joinInner(
                    ['cp' => $this->connection->getTableName('catalog_category_product')],
                    'cp.product_id = e.entity_id',
                    []
                )
                ->where(
                    $productsCollection->getConnection()->prepareSqlCondition(
                        'cp.category_id',
                        [
                            'in' => $this->hashMapPool->getDataMap(
                                DataCategoryHashMap::class,
                                $categoryId
                            )->getAllData($categoryId)
                        ]
                    )
                )->group('e.entity_id');
            $this->hashMap[$categoryId] = $productsCollection->getAllIds();
        }
        return $this->hashMap[$categoryId];
    }

    /**
     * {@inheritdoc}
     */
    public function resetData($categoryId)
    {
        $this->hashMapPool->resetMap(DataCategoryHashMap::class, $categoryId);
        unset($this->hashMap[$categoryId]);
    }
}
