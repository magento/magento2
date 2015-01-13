<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

/**
 * Class Row reindex action
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock\Action
 */
class Row extends \Magento\CatalogInventory\Model\Indexer\Stock\AbstractAction
{
    /**
     * Execute Row reindex
     *
     * @param int|null $id
     * @throws \Magento\CatalogInventory\Exception
     *
     * @return void
     */
    public function execute($id = null)
    {
        if (!isset($id) || empty($id)) {
            throw new \Magento\CatalogInventory\Exception(__('Could not rebuild index for undefined product'));
        }
        try {
            $this->_reindexRows([$id]);
        } catch (\Exception $e) {
            throw new \Magento\CatalogInventory\Exception($e->getMessage(), $e->getCode(), $e);
        }
    }
}
