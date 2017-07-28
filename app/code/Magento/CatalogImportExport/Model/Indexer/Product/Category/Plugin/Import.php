<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\CatalogImportExport\Model\Indexer\Product\Category\Plugin;

/**
 * Class \Magento\CatalogImportExport\Model\Indexer\Product\Category\Plugin\Import
 *
 * @since 2.0.0
 */
class Import
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Processor
     * @since 2.0.0
     */
    protected $_indexerProductCategoryProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Category\Processor $indexerProductCategoryProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Category\Processor $indexerProductCategoryProcessor)
    {
        $this->_indexerProductCategoryProcessor = $indexerProductCategoryProcessor;
    }

    /**
     * After import handler
     *
     * @param \Magento\ImportExport\Model\Import $subject
     * @param boolean $import
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        if (!$this->_indexerProductCategoryProcessor->isIndexerScheduled()) {
            $this->_indexerProductCategoryProcessor->markIndexerAsInvalid();
        }
        return $import;
    }
}
