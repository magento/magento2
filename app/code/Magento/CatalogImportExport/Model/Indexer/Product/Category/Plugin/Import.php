<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Indexer\Product\Category\Plugin;

class Import
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Processor
     */
    protected $_indexerProductCategoryProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Category\Processor $indexerProductCategoryProcessor
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
     */
    public function afterImportSource(\Magento\ImportExport\Model\Import $subject, $import)
    {
        $this->_indexerProductCategoryProcessor->markIndexerAsInvalid();
        return $import;
    }
}
