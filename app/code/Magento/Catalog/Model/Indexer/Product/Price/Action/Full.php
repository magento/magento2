<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Action;

/**
 * Class Full reindex action
 *
 */
class Full extends \Magento\Catalog\Model\Indexer\Product\Price\AbstractAction
{
    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids = null)
    {
        try {
            $this->_defaultIndexerResource->getTableStrategy()->setUseIdxTable(true);
            $this->_emptyTable($this->_defaultIndexerResource->getIdxTable());
            $this->_prepareWebsiteDateTable();
            $this->_prepareTierPriceIndex();

            foreach ($this->getTypeIndexers() as $indexer) {
                $indexer->reindexAll();
            }
            $this->_syncData();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
}
