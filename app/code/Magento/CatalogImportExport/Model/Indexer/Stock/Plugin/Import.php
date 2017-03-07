<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Indexer\Stock\Plugin;

class Import
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockndexerProcessor;

    /**
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockndexerProcessor
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
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        if (!$this->_stockndexerProcessor->isIndexerScheduled()) {
            $this->_stockndexerProcessor->markIndexerAsInvalid();
        }
        return $import;
    }
}
