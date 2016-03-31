<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Cron;

use Magento\NewRelicReporting\Model\Config;
use Magento\Catalog\Api\ProductManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface;
use Magento\Catalog\Api\CategoryManagementInterface;

/**
 * Class ReportCounts
 */
class ReportCounts
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ProductManagementInterface
     */
    protected $productManagement;

    /**
     * @var ConfigurableProductManagementInterface
     */
    protected $configurableManagement;

    /**
     * @var CategoryManagementInterface
     */
    protected $categoryManagement;

    /**
     * @var \Magento\NewRelicReporting\Model\CountsFactory
     */
    protected $countsFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory
     */
    protected $countsCollectionFactory;

    /**
     * Constructor
     *
     * @param Config $config
     * @param ProductManagementInterface $productManagement
     * @param ConfigurableProductManagementInterface $configurableManagement
     * @param CategoryManagementInterface $categoryManagement
     * @param \Magento\NewRelicReporting\Model\CountsFactory $countsFactory
     * @param \Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory $countsCollectionFactory
     */
    public function __construct(
        Config $config,
        ProductManagementInterface $productManagement,
        ConfigurableProductManagementInterface $configurableManagement,
        CategoryManagementInterface $categoryManagement,
        \Magento\NewRelicReporting\Model\CountsFactory $countsFactory,
        \Magento\NewRelicReporting\Model\ResourceModel\Counts\CollectionFactory $countsCollectionFactory
    ) {
        $this->config = $config;
        $this->productManagement = $productManagement;
        $this->configurableManagement = $configurableManagement;
        $this->categoryManagement = $categoryManagement;
        $this->countsFactory = $countsFactory;
        $this->countsCollectionFactory = $countsCollectionFactory;
    }

    /**
     * Updates the count for a specific model in the database
     *
     * @param int $count
     * @param \Magento\NewRelicReporting\Model\Counts $model
     * @param string $type
     * @return void
     */
    protected function updateCount($count, \Magento\NewRelicReporting\Model\Counts $model, $type)
    {
        /** @var \Magento\NewRelicReporting\Model\ResourceModel\Counts\Collection $collection */
        $collection = $this->countsCollectionFactory->create()
            ->addFieldToFilter(
                'type',
                ['eq' => $type]
            )->addOrder(
                'updated_at',
                'DESC'
            )->setPageSize(1);
        $latestUpdate = $collection->getFirstItem();

        if ((!$latestUpdate) || ($count != $latestUpdate->getCount())) {
            $model->setEntityId(null);
            $model->setType($type);
            $model->setCount($count);
            $model->save();
        }
    }

    /**
     * Reports product size to the database reporting_counts table
     *
     * @return void
     */
    protected function reportProductsSize()
    {
        $productCount = $this->productManagement->getCount();
        /** @var \Magento\NewRelicReporting\Model\Counts $model */
        $model = $this->countsFactory->create()->load(Config::PRODUCT_COUNT, 'type');
        $this->updateCount($productCount, $model, Config::PRODUCT_COUNT);
    }

    /**
     * Reports configurable product size to the database reporting_counts table
     *
     * @return void
     */
    protected function reportConfigurableProductsSize()
    {
        $configurableCount = $this->configurableManagement->getCount();
        /** @var \Magento\NewRelicReporting\Model\Counts $model */
        $model = $this->countsFactory->create()->load(Config::CONFIGURABLE_COUNT, 'type');
        $this->updateCount($configurableCount, $model, Config::CONFIGURABLE_COUNT);
    }

    /**
     * Reports number of active products to the database reporting_counts table
     *
     * @return void
     */
    protected function reportProductsActive()
    {
        $productsActiveCount = $this->productManagement->getCount(Status::STATUS_ENABLED);
        /** @var \Magento\NewRelicReporting\Model\Counts $model */
        $model = $this->countsFactory->create()->load(Config::ACTIVE_COUNT, 'type');
        $this->updateCount($productsActiveCount, $model, Config::ACTIVE_COUNT);
    }

    /**
     * Reports category size to the database reporting_counts table
     *
     * @return void
     */
    protected function reportCategorySize()
    {
        $categoryCount = $this->categoryManagement->getCount();
        /** @var \Magento\NewRelicReporting\Model\Counts $model */
        $model = $this->countsFactory->create()->load(Config::CATEGORY_SIZE, 'type');
        $this->updateCount($categoryCount, $model, Config::CATEGORY_SIZE);
    }

    /**
     * Reports Modules and module changes to the database reporting_module_status table
     *
     * @return \Magento\NewRelicReporting\Model\Cron\ReportCounts
     */
    public function report()
    {
        if ($this->config->isNewRelicEnabled()) {
            $this->reportProductsSize();
            $this->reportConfigurableProductsSize();
            $this->reportProductsActive();
            $this->reportCategorySize();
        }

        return $this;
    }
}
