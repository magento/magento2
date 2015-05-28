<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin;

class Import extends \Magento\Catalog\Model\Indexer\Product\Price\Plugin\AbstractPlugin
{
    /**
     * After import handler
     */
    public function afterSaveAdvancedPricing()
    {
        if (!$this->getPriceIndexer()->isScheduled()) {
            $this->invalidateIndexer();
        }
    }

    /**
     * After delete handler
     */
    public function afterDeleteAdvancedPricing()
    {
        if (!$this->getPriceIndexer()->isScheduled()) {
            $this->invalidateIndexer();
        }
    }

    /**
     * Get price indexer
     *
     * @return \Magento\Indexer\Model\IndexerInterface
     */
    protected function getPriceIndexer()
    {
        return $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
    }
}
