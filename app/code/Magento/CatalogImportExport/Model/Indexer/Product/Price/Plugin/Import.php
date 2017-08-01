<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Indexer\Product\Price\Plugin;

/**
 * Class \Magento\CatalogImportExport\Model\Indexer\Product\Price\Plugin\Import
 *
 * @since 2.0.0
 */
class Import extends \Magento\Catalog\Model\Indexer\Product\Price\Plugin\AbstractPlugin
{
    /**
     * After import handler
     *
     * @param \Magento\ImportExport\Model\Import $subject
     * @param bool $result
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $result)
    {
        if (!$this->getPriceIndexer()->isScheduled()) {
            $this->invalidateIndexer();
        }
        return $result;
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
