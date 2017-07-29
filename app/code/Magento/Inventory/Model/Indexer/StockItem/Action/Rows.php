<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem\Action;

use Magento\Inventory\Model\Indexer\StockItem\AbstractAction;

class Rows extends AbstractAction
{
    /**
     * Execute Rows reindex
     *
     * @param int[] $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @return void
     */
    public function execute(array $ids)
    {
        if (empty($ids)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Could not rebuild index for empty stock items array')
            );
        }
        try {
            $this->reindexRows($ids);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
