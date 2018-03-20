<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CategoryProduct
 */
class CategoryProduct extends AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'catalog_category_product_resource';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_product', 'entity_id');
    }

    /**
     * Retrieve distinct product ids, that are linked to categories
     *
     * @return array
     */
    public function getDistinctProductIds()
    {
        $productIdsSelect = $this
            ->getConnection()
            ->select()
            ->from($this->getTable('catalog_category_product'), 'product_id')
            ->distinct('product_id');

        return $this->getConnection()->fetchAll($productIdsSelect);
    }

    /**
     * Retrieve product ids grouped by categories
     *
     * @return array
     */
    public function getProductsIdsGroupedByCategories()
    {
        $productIdsGroupedByCategories = [];
        $productIdsSelect = $this
            ->getConnection()
            ->select()
            ->from(
                $this->getTable('catalog_category_product'),
                ['category_id', 'product_id', 'position']
            );

        $categoriesData = $this->getConnection()->fetchAll($productIdsSelect);

        foreach ($categoriesData as $categoryData) {
            $categoryId = $categoryData['category_id'];
            $productId = $categoryData['product_id'];
            $productIdsGroupedByCategories[$categoryId][$productId] = $categoryData['position'];
        }

        return $productIdsGroupedByCategories;
    }
}
