<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;

/**
 * Class to prepare new tables for new indexer mode
 */
class ModeSwitcher
{
    const XML_PATH_PRICE_DIMENSIONS_MODE = 'indexer/catalog_product_price/dimensions_mode';

    /**
     * TableMaintainer
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
     */
    private $tableMaintainer;

    /**
     * DimensionCollectionFactory
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory
     */
    private $dimensionCollectionFactory;

    /**
     * @var array|null
     */
    private $dimensionsArray;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer
     * @param \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer,
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory $dimensionCollectionFactory
    ) {
        $this->tableMaintainer = $tableMaintainer;
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
    }

    /**
     * Create new tables
     *
     * @param string $currentMode
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function createTables(string $currentMode)
    {
        foreach ($this->getDimensionsArray($currentMode) as $dimensions) {
            if (!empty($dimensions)) {
                $this->tableMaintainer->createTablesForDimensions($dimensions);
            }
        }
    }

    /**
     * Move data from old tables to new
     *
     * @param string $currentMode
     * @param string $previousMode
     *
     * @return void
     */
    public function moveData(string $currentMode, string $previousMode)
    {
        $dimensionsArrayForCurrentMode = $this->getDimensionsArray($currentMode);
        $dimensionsArrayForPreviousMode = $this->getDimensionsArray($previousMode);

        foreach ($dimensionsArrayForCurrentMode as $dimensionsForCurrentMode) {
            $newTable = $this->tableMaintainer->getMainTable($dimensionsForCurrentMode);
            if (empty($dimensionsForCurrentMode)) {
                // new mode is 'none'
                foreach ($dimensionsArrayForPreviousMode as $dimensionsForPreviousMode) {
                    $oldTable = $this->tableMaintainer->getMainTable($dimensionsForPreviousMode);
                    $this->insertFromOldTablesToNew($newTable, $oldTable);
                }
            } else {
                // new mode is not 'none'
                foreach ($dimensionsArrayForPreviousMode as $dimensionsForPreviousMode) {
                    $oldTable = $this->tableMaintainer->getMainTable($dimensionsForPreviousMode);
                    $this->insertFromOldTablesToNew($newTable, $oldTable, $dimensionsForCurrentMode);
                }
            }
        }
    }

    /**
     * Drop old tables
     *
     * @param string $previousMode
     *
     * @return void
     */
    public function dropTables(string $previousMode)
    {
        foreach ($this->getDimensionsArray($previousMode) as $dimensions) {
            if (empty($dimensions)) {
                $this->tableMaintainer->truncateTablesForDimensions($dimensions);
            } else {
                $this->tableMaintainer->dropTablesForDimensions($dimensions);
            }
        }
    }

    /**
     * Get dimensions array
     *
     * @param string $mode
     *
     * @return array
     */
    private function getDimensionsArray(string $mode): \Magento\Framework\Indexer\MultiDimensionProvider
    {
        if (isset($this->dimensionsArray[$mode])) {
            return $this->dimensionsArray[$mode];
        }

        $this->dimensionsArray[$mode] = $this->dimensionCollectionFactory->create($mode);

        return $this->dimensionsArray[$mode];
    }

    /**
     * Insert from old tables data to new
     *
     * @param string $newTable
     * @param string $oldTable
     * @param Dimension[] $dimensions
     *
     * @return void
     */
    private function insertFromOldTablesToNew(string $newTable, string $oldTable, array $dimensions = [])
    {
        $select = $this->tableMaintainer->getConnection()->select()->from($oldTable);

        foreach ($dimensions as $dimension) {
            if ($dimension->getName() === WebsiteDimensionProvider::DIMENSION_NAME) {
                $select->where('website_id = ?', $dimension->getValue());
            }
            if ($dimension->getName() === CustomerGroupDimensionProvider::DIMENSION_NAME) {
                $select->where('customer_group_id = ?', $dimension->getValue());
            }
        }
        $this->tableMaintainer->getConnection()->query(
            $this->tableMaintainer->getConnection()->insertFromSelect(
                $select,
                $newTable,
                [],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }
}
