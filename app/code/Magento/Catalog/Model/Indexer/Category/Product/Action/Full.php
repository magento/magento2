<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Action;

class Full extends \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
{
    /**
     * Refresh entities index
     *
     * @return $this
     */
    public function execute()
    {
        $this->clearTmpData();

        $this->reindex();

        $this->publishData();
        $this->removeUnnecessaryData();

        return $this;
    }

    /**
     * Return select for remove unnecessary data
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getSelectUnnecessaryData()
    {
        return $this->getWriteAdapter()->select()->from(
            $this->getMainTable(),
            []
        )->joinLeft(
            ['t' => $this->getMainTmpTable()],
            $this->getMainTable() .
            '.category_id = t.category_id AND ' .
            $this->getMainTable() .
            '.store_id = t.store_id AND ' .
            $this->getMainTable() .
            '.product_id = t.product_id',
            []
        )->where(
            't.category_id IS NULL'
        );
    }

    /**
     * Remove unnecessary data
     *
     * @return void
     */
    protected function removeUnnecessaryData()
    {
        $this->getWriteAdapter()->query(
            $this->getWriteAdapter()->deleteFromSelect($this->getSelectUnnecessaryData(), $this->getMainTable())
        );
    }

    /**
     * Publish data from tmp to index
     *
     * @return void
     */
    protected function publishData()
    {
        $select = $this->getWriteAdapter()->select()->from($this->getMainTmpTable());

        $queries = $this->prepareSelectsByRange($select, 'category_id');

        foreach ($queries as $query) {
            $this->getWriteAdapter()->query(
                $this->getWriteAdapter()->insertFromSelect(
                    $query,
                    $this->getMainTable(),
                    ['category_id', 'product_id', 'position', 'is_parent', 'store_id', 'visibility'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
                )
            );
        }
    }

    /**
     * Clear all index data
     *
     * @return void
     */
    protected function clearTmpData()
    {
        $this->getWriteAdapter()->delete($this->getMainTmpTable());
    }
}
