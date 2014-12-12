<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $this->_stockndexerProcessor->markIndexerAsInvalid();
        return $import;
    }
}
