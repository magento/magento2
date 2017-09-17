<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import\Data;

/**
 * Import data iterator
 */
class Iterator implements \Iterator
{
    /**
     * Current iterator index
     * @var int
     */
    protected $currentIndex = 0;

    /**
     * @var DataProvider
     */
    protected $importDataProvider = null;

    /**
     * @var DataProviderFactory
     */
    protected $importDataProviderFactory;

    /**
     * Iterator constructor.
     * @param DataProvider $importDataProvider
     */
    public function __construct(DataProviderFactory $importDataProviderFactory) {
        $this->importDataProviderFactory = $importDataProviderFactory;
    }

    /**
     * Returns current row
     *
     * @return array
     */
    public function current()
    {
        if($this->importDataProvider == null) {
            $this->importDataProvider = $this->importDataProviderFactory->create();
        }

        return $this->importDataProvider->getImportDataRow($this->currentIndex);
    }

    /**
     * Moves iterator to next row
     */
    public function next()
    {
        $this->currentIndex++;
    }

    /**
     * Gets current row key
     * @return int
     */
    public function key()
    {
        return $this->currentIndex;
    }

    /**
     * Returns if current row is valid
     * @return int
     */
    public function valid()
    {
        return $this->current() !== null;
    }

    /**
     * Rewinds iterator to first row
     */
    public function rewind()
    {
        $this->currentIndex = 0;
    }
}
