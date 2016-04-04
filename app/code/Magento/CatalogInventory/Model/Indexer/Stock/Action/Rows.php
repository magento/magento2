<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer\Stock\Action;

/**
 * Class Rows reindex action for mass actions
 *
 * @package Magento\CatalogInventory\Model\Indexer\Stock\Action
 */
class Rows extends \Magento\CatalogInventory\Model\Indexer\Stock\AbstractAction
{
    /**
     * Execute Rows reindex
     *
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    public function execute($ids)
    {
        if (empty($ids)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        try {
            $this->_reindexRows($ids);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
