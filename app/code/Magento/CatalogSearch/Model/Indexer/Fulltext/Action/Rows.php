<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

class Rows extends \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
{
    /**
     * Refresh entities index
     *
     * @param int[] $entityIds
     * @return void
     */
    public function reindex(array $entityIds = [])
    {
        // Index basic products
        $this->rebuildIndex($entityIds);
        // Index parent products
        $this->rebuildIndex($this->getProductIdsFromParents($entityIds));
    }

    /**
     * Get parents IDs of product IDs to be re-indexed
     *
     * @param int[] $entityIds
     * @return int[]
     */
    protected function getProductIdsFromParents(array $entityIds)
    {
        return $this->getWriteAdapter()->select()
            ->from($this->getTable('catalog_product_relation'), 'parent_id')
            ->distinct(true)
            ->where('child_id IN (?)', $entityIds)
            ->where('parent_id NOT IN (?)', $entityIds)
            ->query()->fetchAll(\Zend_Db::FETCH_COLUMN);
    }
}
