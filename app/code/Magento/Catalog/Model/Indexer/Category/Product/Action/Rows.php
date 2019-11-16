<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

/**
 * Reindex multiple rows action.
 *
 * @package Magento\Catalog\Model\Indexer\Category\Product\Action
 */
class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by categories
     *
     * @var int[]
     */
    protected $limitationByCategories;

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     */
    public function execute(array $entityIds = [], $useTempTable = false)
    {
        foreach ($entityIds as $entityId) {
            $this->limitationByCategories[] = (int)$entityId;
            $path = $this->getPathFromCategoryId($entityId);
            if (!empty($path)) {
                $pathIds = explode('/', $path);
                foreach ($pathIds as $pathId) {
                    $this->limitationByCategories[] = (int)$pathId;
                }
            }
        }
        $this->limitationByCategories = array_unique($this->limitationByCategories);
        $this->useTempTable = $useTempTable;
        $this->removeEntries();
        $this->reindex();

        return $this;
    }

    /**
     * Return array of all category root IDs + tree root ID
     *
     * @param \Magento\Store\Model\Store $store
     * @return int
     */
    private function getRootCategoryId($store)
    {
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
            $rootId = $store->getRootCategoryId();
        }
        return $rootId;
    }

    /**
     * Remove index entries before reindexation
     *
     * @return void
     */
    private function removeEntries()
    {
        foreach ($this->storeManager->getStores() as $store) {
            $removalCategoryIds = array_diff($this->limitationByCategories, [$this->getRootCategoryId($store)]);
            $this->connection->delete(
                $this->getIndexTable($store->getId()),
                ['category_id IN (?)' => $removalCategoryIds]
            );
        }
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
        return $select->where('cc.entity_id IN (?)', $this->limitationByCategories);
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
        return $select->where('cc.entity_id IN (?)', $this->limitationByCategories);
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
     * Check whether indexation of root category is needed
     *
     * @return bool
     */
    protected function isIndexRootCategoryNeeded()
    {
        return false;
    }
}
