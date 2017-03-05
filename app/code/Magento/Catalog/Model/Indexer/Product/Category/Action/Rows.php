<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Category\Action;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Indexer\CacheContext;

class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by products
     *
     * @var int[]
     */
    protected $limitationByProducts;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     */
    public function execute(array $entityIds = [], $useTempTable = false)
    {
        $this->limitationByProducts = $entityIds;
        $this->useTempTable = $useTempTable;

        $this->removeEntries();

        $this->reindex();

        $this->registerProducts($entityIds);
        $this->registerCategories($entityIds);

        return $this;
    }

    /**
     * Register affected products
     * 
     * @param array $entityIds
     * @return void
     */
    private function registerProducts($entityIds)
    {
        $this->getCacheContext()->registerEntities(Product::CACHE_TAG, $entityIds);
    }

    /**
     * Register categories assigned to products
     *
     * @param array $entityIds
     * @return void
     */
    private function registerCategories($entityIds)
    {
        $categories = $this->connection->fetchCol(
            $this->connection->select()
                ->from($this->getMainTable(), ['category_id'])
                ->where('product_id IN (?)', $entityIds)
                ->distinct()
        );

        if ($categories) {
            $this->getCacheContext()->registerEntities(Category::CACHE_TAG, $categories);
        }
    }

    /**
     * Remove index entries before reindexation
     *
     * @return void
     */
    protected function removeEntries()
    {
        $this->connection->delete(
            $this->getMainTable(),
            ['product_id IN (?)' => $this->limitationByProducts]
        );
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getNonAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        $select = parent::getNonAnchorCategoriesSelect($store);
        return $select->where('ccp.product_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAnchorCategoriesSelect(\Magento\Store\Model\Store $store)
    {
        $select = parent::getAnchorCategoriesSelect($store);
        return $select->where('ccp.product_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Get select for all products
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     */
    protected function getAllProducts(\Magento\Store\Model\Store $store)
    {
        $select = parent::getAllProducts($store);
        return $select->where('cp.entity_id IN (?)', $this->limitationByProducts);
    }

    /**
     * Check whether select ranging is needed
     *
     * @return bool
     */
    protected function isRangingNeeded()
    {
        return false;
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated
     */
    private function getCacheContext()
    {
        if ($this->cacheContext === null) {
            $this->cacheContext = \Magento\Framework\App\ObjectManager::getInstance()->get(CacheContext::class);
        }
        return $this->cacheContext;
    }
}
