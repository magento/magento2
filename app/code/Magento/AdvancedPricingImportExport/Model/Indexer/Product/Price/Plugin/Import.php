<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

class Import extends \Magento\Catalog\Model\Indexer\Product\Price\Plugin\AbstractPlugin
{
    /**
     * After import handler
     *
     * @param AdvancedPricing $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveAdvancedPricing(AdvancedPricing $subject)
    {
        if (!$this->getPriceIndexer()->isScheduled()) {
            $this->invalidateIndexer();
        }
    }

    /**
     * After delete handler
     *
     * @param AdvancedPricing $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteAdvancedPricing(AdvancedPricing $subject)
    {
        if (!$this->getPriceIndexer()->isScheduled()) {
            $this->invalidateIndexer();
        }
    }

    /**
     * Get price indexer
     *
     * @return \Magento\Framework\Indexer\IndexerInterface
     */
    protected function getPriceIndexer()
    {
        return $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
    }
}
