<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Framework\Search\Request\Dimension;

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
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $customerGroupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

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
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configReader,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configWriter,
        \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer $tableMaintainer,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->configReader = $configReader;
        $this->configWriter = $configWriter;
        $this->tableMaintainer = $tableMaintainer;
        $this->websiteRepository = $websiteRepository;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Create new tables
     *
     * @param string $currentMode
     *
     * @return void
     */
    public function createTables($currentMode)
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
    public function moveData($currentMode, $previousMode)
    {
        //move data
    }

    /**
     * Drop old tables
     *
     * @param string $previousMode
     *
     * @return void
     */
    public function dropTables($previousMode)
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
     * @param string $previousMode
     *
     * @return array
     */
    private function getDimensionsArray($mode)
    {
        if (isset($this->dimensionsArray[$mode])) {
            return $this->dimensionsArray[$mode];
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $customerGroups = $this->customerGroupRepository->getList($searchCriteria)->getItems();

        $dimensionsArray = [];
        if ($mode !== self::INPUT_KEY_NONE) {
            foreach ($this->websiteRepository->getList() as $website) {
                foreach ($customerGroups as $customerGroup) {
                    $websiteDimension = new Dimension('website', $website->getId());
                    $customerGroupDimension = new Dimension('group', $customerGroup->getId());
                    if ($mode === self::INPUT_KEY_WEBSITE) {
                        $dimensionsArray[] = [$websiteDimension];
                    } elseif ($mode === self::INPUT_KEY_CUSTOMER_GROUP) {
                        $dimensionsArray[] = [$customerGroupDimension];
                    } else {
                        $dimensionsArray[] = [$websiteDimension, $customerGroupDimension];
                    }
                }
            }
        } else {
            $dimensionsArray[] = [];
        }

        $this->dimensionsArray[$mode] = $dimensionsArray;
        return $this->dimensionsArray[$mode];
    }
}
