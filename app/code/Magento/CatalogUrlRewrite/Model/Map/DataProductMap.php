<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Map that holds data for products ids from a category and subcategories
 */
class DataProductMap implements DataMapInterface
{
    /** @var array */
    private $data = [];

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var DataMapPoolInterface */
    private $dataMapPool;

    /** @var ResourceConnection */
    private $connection;

    /**
     * @param CollectionFactory $collectionFactory
     * @param DataMapPoolInterface $dataMapPool
     * @param ResourceConnection $connection
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DataMapPoolInterface $dataMapPool,
        ResourceConnection $connection
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dataMapPool = $dataMapPool;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllData($categoryId)
    {
        if (empty($this->data[$categoryId])) {
            $this->data[$categoryId] = $this->generateData($categoryId);
        }
        return $this->data[$categoryId];
    }

    /**
     * {@inheritdoc}
     */
    public function getData($categoryId, $key)
    {
        $this->getAllData($categoryId);
        return $this->data[$categoryId][$key];
    }

    /**
     * Queries the database and returns results
     *
     * @param int $categoryId
     * @return array
     */
    private function generateData($categoryId)
    {
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
                        'in' => $this->dataMapPool->getDataMap(
                            DataCategoryMap::class,
                            $categoryId
                        )->getAllData($categoryId)
                    ]
                )
            )->group('e.entity_id');

        return $productsCollection->getAllIds();
    }

    /**
     * {@inheritdoc}
     */
    public function resetData($categoryId)
    {
        $this->dataMapPool->resetDataMap(DataCategoryMap::class, $categoryId);
        unset($this->data[$categoryId]);
        if (empty($this->data)) {
            $this->data = [];
        }
    }
}
