<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer as CategoryProductTableMaintainer;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class GetCategories
{
    /**
     * @param ResourceConnection $resource
     * @param CategoryProductTableMaintainer $categoryProductTableMaintainer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly CategoryProductTableMaintainer $categoryProductTableMaintainer,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Returns list of categories ids for provided products
     *
     * @param int[] $productList
     * @return int[]
     */
    public function execute(array $productList): array
    {
        $connection = $this->resource->getConnection();
        $categories = [];
        foreach ($this->storeManager->getStores() as $store) {
            $select = $connection->select()->from(
                ['category_product_index' => $this->categoryProductTableMaintainer->getMainTable((int)$store->getId())],
                ['category_product_index.category_id']
            );
            $select->where('category_product_index.product_id IN (?)', $productList, \Zend_Db::INT_TYPE);
            $select->distinct(true);

            $categories += array_fill_keys($connection->fetchCol($select), true);
        }

        return array_keys($categories);
    }
}
