<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

class Import
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(\Magento\Framework\Indexer\IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * After import handler
     *
     * @param AdvancedPricing $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSaveAdvancedPricing(AdvancedPricing $subject)
    {
        $this->invalidateIndexer();
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
        $this->invalidateIndexer();
    }

    /**
     * Invalidate indexer
     *
     * @return void
     */
    private function invalidateIndexer()
    {
        $priceIndexer = $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
        if (!$priceIndexer->isScheduled()) {
            $priceIndexer->invalidate();
        }
    }
}
