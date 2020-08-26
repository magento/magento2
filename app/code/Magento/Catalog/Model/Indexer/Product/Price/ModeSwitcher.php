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
use Magento\Indexer\Model\DimensionModes;
use Magento\Indexer\Model\DimensionMode;

/**
 * Class to prepare new tables for new indexer mode
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModeSwitcher implements \Magento\Indexer\Model\ModeSwitcherInterface
{
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
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration
     */
    private $dimensionModeConfiguration;

    /**
     * @var ModeSwitcherConfiguration
     */
    private $modeSwitcherConfiguration;

    /**
     * @param TableMaintainer $tableMaintainer
     * @param DimensionCollectionFactory $dimensionCollectionFactory
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     * @param ModeSwitcherConfiguration $modeSwitcherConfiguration
     */
    public function __construct(
        TableMaintainer $tableMaintainer,
        DimensionCollectionFactory $dimensionCollectionFactory,
        DimensionModeConfiguration $dimensionModeConfiguration,
        ModeSwitcherConfiguration $modeSwitcherConfiguration
    ) {
        $this->tableMaintainer = $tableMaintainer;
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
        $this->modeSwitcherConfiguration = $modeSwitcherConfiguration;
    }

    /**
     * @inheritdoc
     */
    public function getDimensionModes(): DimensionModes
    {
        $dimensionsList = [];
        foreach ($this->dimensionModeConfiguration->getDimensionModes() as $dimension => $modes) {
            $dimensionsList[] = new DimensionMode($dimension, $modes);
        }

        return new DimensionModes($dimensionsList);
    }

    /**
     * @inheritdoc
     */
    public function switchMode(string $currentMode, string $previousMode)
    {
        //Create new tables and move data
        $this->createTables($currentMode);
        $this->moveData($currentMode, $previousMode);

        //Change config options
        $this->modeSwitcherConfiguration->saveMode($currentMode);

        //Delete old tables
        $this->dropTables($previousMode);
    }

    /**
     * Create new tables
     *
     * @param string $currentMode
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
     * @return void
     */
    public function moveData(string $currentMode, string $previousMode)
    {
        $dimensionsArrayForCurrentMode = $this->getDimensionsArray($currentMode);
        $dimensionsArrayForPreviousMode = $this->getDimensionsArray($previousMode);

        foreach ($dimensionsArrayForCurrentMode as $dimensionsForCurrentMode) {
            $newTable = $this->tableMaintainer->getMainTableByDimensions($dimensionsForCurrentMode);
            if (empty($dimensionsForCurrentMode)) {
                // new mode is 'none'
                foreach ($dimensionsArrayForPreviousMode as $dimensionsForPreviousMode) {
                    $oldTable = $this->tableMaintainer->getMainTableByDimensions($dimensionsForPreviousMode);
                    $this->insertFromOldTablesToNew($newTable, $oldTable);
                }
            } else {
                // new mode is not 'none'
                foreach ($dimensionsArrayForPreviousMode as $dimensionsForPreviousMode) {
                    $oldTable = $this->tableMaintainer->getMainTableByDimensions($dimensionsForPreviousMode);
                    $this->insertFromOldTablesToNew($newTable, $oldTable, $dimensionsForCurrentMode);
                }
            }
        }
    }

    /**
     * Drop old tables
     *
     * @param string $previousMode
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
     * @return \Magento\Framework\Indexer\MultiDimensionProvider
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
