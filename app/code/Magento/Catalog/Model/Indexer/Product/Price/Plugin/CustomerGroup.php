<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Catalog\Model\Indexer\Product\Price\UpdateIndexInterface;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Store\Model\Store;

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
     * @var WebsiteCollectionFactory
     */
    private $websiteCollectionFactory;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $configReader;

    /**
     * Constructor
     *
     * @param UpdateIndexInterface $updateIndex
     * @param TableMaintainer $tableMaintainer
     * @param DimensionFactory $dimensionFactory
     * @param WebsiteCollectionFactory $websiteCollectionFactory
     * @param ScopeConfigInterface $configReader
     */
    public function __construct(
        UpdateIndexInterface $updateIndex,
        TableMaintainer $tableMaintainer,
        DimensionFactory $dimensionFactory,
        WebsiteCollectionFactory $websiteCollectionFactory,
        ScopeConfigInterface $configReader
    ) {
        $this->updateIndex = $updateIndex;
        $this->tableMaintainer = $tableMaintainer;
        $this->dimensionFactory = $dimensionFactory;
        $this->websiteCollectionFactory = $websiteCollectionFactory;
        $this->configReader = $configReader;
    }

    /**
     * Update price index after customer group saved
     *
     * @param GroupRepositoryInterface $subject
     * @param \Closure $proceed
     * @param GroupInterface $result
     * @return GroupInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        GroupRepositoryInterface $subject,
        \Closure $proceed,
        GroupInterface $group
    ) {
        $isGroupNew = !$group->getId();
        $group = $proceed($group);
        if ($isGroupNew) {
            foreach ($this->getAffectedDimensions($group->getId()) as $dimensions) {
                $this->tableMaintainer->createTablesForDimensions($dimensions);
            }
        }
        $this->updateIndex->update($group, $isGroupNew);
        return $group;
    }

    /**
     * @param GroupRepositoryInterface $subject
     * @param bool $result
     * @param string $groupId
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(GroupRepositoryInterface $subject, bool $result, string $groupId)
    {
        foreach ($this->getAffectedDimensions((int)$groupId) as $dimensions) {
            $this->tableMaintainer->dropTablesForDimensions($dimensions);
        }

        return $result;
    }

    /**
     * Get affected dimensions
     *
     * @param int $groupId
     *
     * @return array
     */
    private function getAffectedDimensions(int $groupId): array
    {
        $return = [];

        switch ($this->configReader->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE)) {
            case ModeSwitcher::INPUT_KEY_CUSTOMER_GROUP:
                $return = [$this->dimensionFactory->create(CustomerGroupDataProvider::DIMENSION_NAME, $groupId)];
                break;
            case ModeSwitcher::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP:
                $websiteIds = $this->websiteCollectionFactory->create()
                    ->addFieldToFilter('code', ['neq' => Store::ADMIN_CODE])
                    ->getAllIds();

                foreach ($websiteIds as $websiteId) {
                    $return[] = [
                        $this->dimensionFactory->create(
                            WebsiteDataProvider::DIMENSION_NAME,
                            (string)$websiteId
                        ),
                        $this->dimensionFactory->create(
                            CustomerGroupDataProvider::DIMENSION_NAME,
                            (string)$groupId
                        )
                    ];
                }
                break;
        }
        return $return;
    }
}
