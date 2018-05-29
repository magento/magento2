<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;

/**
 * Class to prepare new tables for new indexer mode
 */
class ModeSwitcher
{
    const INPUT_KEY_NONE = 'none';
    const INPUT_KEY_WEBSITE = 'website';
    const INPUT_KEY_CUSTOMER_GROUP = 'customer_group';
    const INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP = 'website_and_customer_group';
    const XML_PATH_PRICE_DIMENSIONS_MODE = 'indexer/catalog_product_price/dimensions_mode';

    /**
     * ScopeConfigInterface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $configReader;

    /**
     * ConfigInterface
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configWriter;

    /**
     * TableMaintainer
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer
     */
    private $tableMaintainer;

    /**
     * WebsiteRepositoryInterface
     *
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * GroupRepositoryInterface
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * SearchCriteriaBuilder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * DimensionCollectionFactory
     *
     * @var \Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory
     */
    private $dimensionProviderFactory;

    /**
     * @var array|null
     */
    private $dimensionsArray;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configReader
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter
     * @param \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer
     * @param \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository
     * @param \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory $dimensionProviderFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configReader,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter,
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\DimensionProviderFactory $dimensionProviderFactory
    ) {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->tableMaintainer = $tableMaintainer;
        $this->websiteRepository = $websiteRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dimensionProviderFactory = $dimensionProviderFactory;
    }

    /**
     * Create new tables
     *
     * @param string $currentMode
     *
     * @return void
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
    private function getDimensionsArray(string $mode): \Magento\Framework\Indexer\MultiDimensionProviderInterface
    {
        if (isset($this->dimensionsArray[$mode])) {
            return $this->dimensionsArray[$mode];
        }

        $this->dimensionsArray[$mode] = $this->dimensionProviderFactory->createByMode($mode);

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
            if ($dimension->getName() === WebsiteDataProvider::DIMENSION_NAME) {
                $select->where('website_id = ?', $dimension->getValue());
            }
            if ($dimension->getName() === CustomerGroupDataProvider::DIMENSION_NAME) {
                $select->where('customer_group_id = ?', $dimension->getValue());
            }
        }
        $this->tableMaintainer->getConnection()->query(
            $this->tableMaintainer->getConnection()->insertFromSelect($select, $newTable)
        );
    }
}
