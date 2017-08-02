<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Indexer\Product\Eav\Plugin;

/**
 * Class \Magento\CatalogImportExport\Model\Indexer\Product\Eav\Plugin\Import
 *
 * @since 2.0.0
 */
class Import
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     * @since 2.0.0
     */
    protected $_indexerEavProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor)
    {
        $this->_indexerEavProcessor = $indexerEavProcessor;
    }

    /**
     * After import handler
     *
     * @param \Magento\ImportExport\Model\Import $subject
     * @param Object $import
     *
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        if (!$this->_indexerEavProcessor->isIndexerScheduled()) {
            $this->_indexerEavProcessor->markIndexerAsInvalid();
        }
        return $import;
    }
}
