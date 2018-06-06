<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Customer\Model\Indexer\MultiDimensional\CustomerGroupDataProvider;
use Magento\Store\Model\Indexer\MultiDimensional\WebsiteDataProvider;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

class Website
{
    /**
     * @var Processor
     */
    private $processor;

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
     * @var CustomerGroupCollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * ScopeConfigInterface
     *
     * @var ScopeConfigInterface
     */
    private $configReader;

    /**
     * @param Processor $processor
     * @param TableMaintainer $tableMaintainer
     * @param DimensionFactory $dimensionFactory
     * @param CustomerGroupCollectionFactory $customerGroupCollectionFactory
     * @param ScopeConfigInterface $configReader
     */
    public function __construct(
        Processor $processor,
        TableMaintainer $tableMaintainer,
        DimensionFactory $dimensionFactory,
        CustomerGroupCollectionFactory $customerGroupCollectionFactory,
        ScopeConfigInterface $configReader
    ) {
        $this->processor = $processor;
        $this->tableMaintainer = $tableMaintainer;
        $this->dimensionFactory = $dimensionFactory;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->configReader = $configReader;
    }

    /**
     * @param AbstractDb $subject
     * @param AbstractDb $objectResource
     * @param AbstractModel $website
     *
     * @return AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(AbstractDb $subject, AbstractDb $objectResource, AbstractModel $website)
    {
        $this->processor->markIndexerAsInvalid();

        foreach ($this->getAffectedDimensions($website->getId()) as $dimensions) {
            $this->tableMaintainer->dropTablesForDimensions($dimensions);
        }

        return $objectResource;
    }

    /**
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
     * @param int $websiteId
     *
     * @return array
     */
    private function getAffectedDimensions(int $websiteId): array
    {
        $return = [];

        switch ($this->configReader->getValue(ModeSwitcher::XML_PATH_PRICE_DIMENSIONS_MODE)) {
            case ModeSwitcher::INPUT_KEY_WEBSITE:
                $return = [$this->dimensionFactory->create(WebsiteDataProvider::DIMENSION_NAME, $websiteId)];
                break;
            case ModeSwitcher::INPUT_KEY_WEBSITE_AND_CUSTOMER_GROUP:
                $customerGroupIds = $this->customerGroupCollectionFactory->create()->getAllIds();
                foreach ($customerGroupIds as $customerGroupId) {
                    $return[] = [
                        $this->dimensionFactory->create(
                            WebsiteDataProvider::DIMENSION_NAME,
                            (string)$websiteId
                        ),
                        $this->dimensionFactory->create(
                            CustomerGroupDataProvider::DIMENSION_NAME,
                            (string)$customerGroupId
                        )
                    ];
                }
                break;
        }
        return $return;
    }
}
