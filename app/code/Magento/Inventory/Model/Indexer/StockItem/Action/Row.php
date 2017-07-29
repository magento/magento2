<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem\Action;

use Magento\Inventory\Model\Indexer\StockItem\AbstractAction;

class Row extends AbstractAction
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
        if (!isset($id) || null === $id) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We can\'t rebuild the index for an undefined stock item .')
            );
        }
        try {
            $this->reindexRows([$id]);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
