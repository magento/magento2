<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Indexer\Stock\Plugin;

/**
 * Class \Magento\CatalogImportExport\Model\Indexer\Stock\Plugin\Import
 *
 * @since 2.0.0
 */
class Import
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     * @since 2.0.0
     */
    protected $_stockndexerProcessor;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockndexerProcessor
     * @since 2.0.0
     */
    public function __construct(\Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockndexerProcessor)
    {
        $this->_stockndexerProcessor = $stockndexerProcessor;
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
        if (!$this->_stockndexerProcessor->isIndexerScheduled()) {
            $this->_stockndexerProcessor->markIndexerAsInvalid();
        }
        return $import;
    }
}
