<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

/**
 * Class Row reindex action
 */
class Row extends \Magento\CatalogInventory\Model\Indexer\Stock\AbstractAction
{
    /**
     * Execute Row reindex
     *
     * @param int|null $id
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        try {
            $this->_reindexRows([$id]);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
