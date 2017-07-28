<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin;

use Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

/**
 * Class \Magento\AdvancedPricingImportExport\Model\Indexer\Product\Price\Plugin\Import
 *
 * @since 2.0.0
 */
class Import extends \Magento\Catalog\Model\Indexer\Product\Price\Plugin\AbstractPlugin
{
    /**
     * After import handler
     *
     * @param AdvancedPricing $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function getPriceIndexer()
    {
        return $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID);
    }
}
