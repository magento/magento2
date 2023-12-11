<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Catalog\Model\Indexer\Product\Price\UpdateIndexInterface;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Update catalog_product_index_price table after delete or save customer group
 */
class CustomerGroup
{
    /**
     * @var UpdateIndexInterface
     */
    private $updateIndex;

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
     * @var WebsiteDimensionProvider
     */
    private $websiteDimensionProvider;

    /**
     * @param UpdateIndexInterface $updateIndex
     * @param TableMaintainer $tableMaintainer
     * @param DimensionFactory $dimensionFactory
     * @param DimensionModeConfiguration $dimensionModeConfiguration
     * @param WebsiteDimensionProvider $websiteDimensionProvider
     */
    public function __construct(
        UpdateIndexInterface $updateIndex,
        TableMaintainer $tableMaintainer,
        DimensionFactory $dimensionFactory,
        DimensionModeConfiguration $dimensionModeConfiguration,
        WebsiteDimensionProvider $websiteDimensionProvider
    ) {
        $this->updateIndex = $updateIndex;
        $this->tableMaintainer = $tableMaintainer;
        $this->dimensionFactory = $dimensionFactory;
        $this->dimensionModeConfiguration = $dimensionModeConfiguration;
        $this->websiteDimensionProvider = $websiteDimensionProvider;
    }

    /**
     * Update price index after customer group saved
     *
     * @param GroupRepositoryInterface $subject
     * @param \Closure $proceed
     * @param GroupInterface $group
     *
     * @return GroupInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        GroupRepositoryInterface $subject,
        \Closure $proceed,
        GroupInterface $group
    ) {
        $isGroupNew = $group->getId() === null;
        $group = $proceed($group);
        if ($isGroupNew) {
            foreach ($this->getAffectedDimensions((string)$group->getId()) as $dimensions) {
                $this->tableMaintainer->createTablesForDimensions($dimensions);
            }
        }
        $this->updateIndex->update($group, $isGroupNew);
        return $group;
    }

    /**
     * Update price index after customer group deleted
     *
     * @param GroupRepositoryInterface $subject
     * @param bool $result
     * @param string $groupId
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, bool $result, string $groupId)
    {
        foreach ($this->getAffectedDimensions($groupId) as $dimensions) {
            $this->tableMaintainer->dropTablesForDimensions($dimensions);
        }

        return $result;
    }

    /**
     * Get affected dimensions
     *
     * @param string $groupId
     * @return Dimension[][]
     */
    private function getAffectedDimensions(string $groupId): array
    {
        $currentDimensions = $this->dimensionModeConfiguration->getDimensionConfiguration();
        // do not return dimensions if Customer Group dimension is not present in configuration
        if (!in_array(CustomerGroupDimensionProvider::DIMENSION_NAME, $currentDimensions, true)) {
            return [];
        }
        $customerGroupDimension = $this->dimensionFactory->create(
            CustomerGroupDimensionProvider::DIMENSION_NAME,
            $groupId
        );

        $dimensions = [];
        if (in_array(WebsiteDimensionProvider::DIMENSION_NAME, $currentDimensions, true)) {
            foreach ($this->websiteDimensionProvider as $websiteDimension) {
                $dimensions[] = [
                    $customerGroupDimension,
                    $websiteDimension
                ];
            }
        } else {
            $dimensions[] = [$customerGroupDimension];
        }

        return $dimensions;
    }
}
