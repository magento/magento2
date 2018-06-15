<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

class Website
{
    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * DimensionFactory
     *
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var DimensionModeConfiguration
     */
    private $dimensionModeConfiguration;

    /**
     * @var CustomerGroupDataProvider
     */
    private $customerGroupDataProvider;

    /**
     * @param TableMaintainer $tableMaintainer
     * @param DimensionFactory $dimensionFactory
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     * @param CustomerGroupDataProvider $customerGroupDataProvider
     */
    public function __construct(
        TableMaintainer $tableMaintainer,
        DimensionFactory $dimensionFactory,
        DimensionModeConfiguration $dimensionModeConfiguration,
        CustomerGroupDataProvider $customerGroupDataProvider
    ) {
        $this->tableMaintainer = $tableMaintainer;
        $this->dimensionFactory = $dimensionFactory;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
        $this->customerGroupDataProvider = $customerGroupDataProvider;
    }

    /**
     * Update price index after website deleted
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     * @param AbstractModel $website
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(AbstractDb $subject, AbstractDb $objectResource, AbstractModel $website)
    {
        foreach ($this->getAffectedDimensions($website->getId()) as $dimensions) {
            $this->tableMaintainer->dropTablesForDimensions($dimensions);
        }

        return $objectResource;
    }

    /**
     * Update price index after website created
     *
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     * @param AbstractModel $website
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(AbstractDb $subject, AbstractDb $objectResource, AbstractModel $website)
    {
        if ($website->isObjectNew()) {
            foreach ($this->getAffectedDimensions($website->getId()) as $dimensions) {
                $this->tableMaintainer->createTablesForDimensions($dimensions);
            }
        }

        return $objectResource;
    }

    /**
     * Get affected dimensions
     *
     * @param string $websiteId
     *
     * @return Dimension[][]
     */
    private function getAffectedDimensions(string $websiteId): array
    {
        $currentDimensions = $this->dimensionModeConfiguration->getDimensionConfiguration();
        // do not return dimensions if Website dimension is not present in configuration
        if (!in_array(WebsiteDataProvider::DIMENSION_NAME, $currentDimensions, true)) {
            return [];
        }
        $websiteDimension = $this->dimensionFactory->create(
            WebsiteDataProvider::DIMENSION_NAME,
            $websiteId
        );

        $dimensions = [];
        if (in_array(CustomerGroupDataProvider::DIMENSION_NAME, $currentDimensions, true)) {
            foreach ($this->customerGroupDataProvider as $customerGroupDimension) {
                $dimensions[] = [
                    $customerGroupDimension,
                    $websiteDimension
                ];
            }
        } else {
            $dimensions[] = [$websiteDimension];
        }

        return $dimensions;
    }
}
