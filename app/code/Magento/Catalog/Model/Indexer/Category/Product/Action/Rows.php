<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

/**
 * Class \Magento\Catalog\Model\Indexer\Category\Product\Action\Rows
 *
 * @since 2.0.0
 */
class Rows extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Limitation by categories
     *
     * @var int[]
     * @since 2.0.0
     */
    protected $limitationByCategories;

    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @param bool $useTempTable
     * @return $this
     * @since 2.0.0
     */
    public function execute(array $entityIds = [], $useTempTable = false)
    {
        $this->limitationByCategories = $entityIds;
        $this->useTempTable = $useTempTable;

        $this->removeEntries();

        $this->reindex();

        return $this;
    }

    /**
     * Return array of all category root IDs + tree root ID
     *
     * @return int[]
     * @since 2.0.0
     */
    protected function getRootCategoryIds()
    {
        $rootIds = [\Magento\Catalog\Model\Category::TREE_ROOT_ID];
        foreach ($this->storeManager->getStores() as $store) {
            if ($this->getPathFromCategoryId($store->getRootCategoryId())) {
                $rootIds[] = $store->getRootCategoryId();
            }
        }
        return $rootIds;
    }

    /**
     * Remove index entries before reindexation
     *
     * @return void
     * @since 2.0.0
     */
    protected function removeEntries()
    {
        $removalCategoryIds = array_diff($this->limitationByCategories, $this->getRootCategoryIds());
        $this->connection->delete($this->getMainTable(), ['category_id IN (?)' => $removalCategoryIds]);
    }

    /**
     * Retrieve select for reindex products of non anchor categories
     *
     * @param \Magento\Store\Model\Store $store
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function isRangingNeeded()
    {
        return false;
    }

    /**
     * Check whether indexation of root category is needed
     *
     * @return bool
     * @since 2.0.0
     */
    protected function isIndexRootCategoryNeeded()
    {
        return false;
    }
}
