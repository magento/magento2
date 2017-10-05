<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\State as FlatState;

class Import
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    private $flatState;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $flatState
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        FlatState $flatState
    ) {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->flatState = $flatState;
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
        if ($this->flatState->isFlatEnabled() && !$this->_productFlatIndexerProcessor->isIndexerScheduled()) {
            $this->_productFlatIndexerProcessor->markIndexerAsInvalid();
        }

        return $import;
    }
}
