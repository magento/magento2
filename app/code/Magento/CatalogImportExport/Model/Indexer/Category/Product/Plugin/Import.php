<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogImportExport\Model\Indexer\Category\Product\Plugin;

class Import
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\Processor
     */
    protected $_indexerCategoryProductProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Category\Product\Processor $indexerCategoryProductProcessor
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Category\Product\Processor $indexerCategoryProductProcessor)
    {
        $this->_indexerCategoryProductProcessor = $indexerCategoryProductProcessor;
    }

    /**
     * After import handler
     *
     * @param \Magento\ImportExport\Model\Import $subject
     * @param boolean $import
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        if (!$this->_indexerCategoryProductProcessor->isIndexerScheduled()) {
            $this->_indexerCategoryProductProcessor->markIndexerAsInvalid();
        }
        return $import;
    }
}
