<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model;

use Magento\Catalog\Api\ProductManagementInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Customer\Api\CustomerManagementInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Api\StoreManagementInterface;

/**
 * Class \Magento\NewRelicReporting\Model\Counter
 *
 * @since 2.0.0
 */
class Counter
{
    /**
     * @var ProductManagementInterface
     * @since 2.0.0
     */
    protected $productManagement;

    /**
     * @var ConfigurableProductManagementInterface
     * @since 2.0.0
     */
    protected $configurableManagement;

    /**
     * @var CategoryManagementInterface
     * @since 2.0.0
     */
    protected $categoryManagement;

    /**
     * @var CustomerManagementInterface
     * @since 2.0.0
     */
    protected $customerManagement;

    /**
     * @var WebsiteManagementInterface
     * @since 2.0.0
     */
    protected $websiteManagement;

    /**
     * @var StoreManagementInterface
     * @since 2.0.0
     */
    protected $storeManagement;

    /**
     * Constructor
     *
     * @param ProductManagementInterface $productManagement
     * @param ConfigurableProductManagementInterface $configurableManagement
     * @param CategoryManagementInterface $categoryManagement
     * @param CustomerManagementInterface $customerManagement
     * @param WebsiteManagementInterface $websiteManagement
     * @param StoreManagementInterface $storeManagement
     * @since 2.0.0
     */
    public function __construct(
        ProductManagementInterface $productManagement,
        ConfigurableProductManagementInterface $configurableManagement,
        CategoryManagementInterface $categoryManagement,
        CustomerManagementInterface $customerManagement,
        WebsiteManagementInterface $websiteManagement,
        StoreManagementInterface $storeManagement
    ) {
        $this->productManagement = $productManagement;
        $this->configurableManagement = $configurableManagement;
        $this->categoryManagement = $categoryManagement;
        $this->customerManagement = $customerManagement;
        $this->websiteManagement = $websiteManagement;
        $this->storeManagement = $storeManagement;
    }

    /**
     * Get count of all products, no conditions
     *
     * @return int
     * @since 2.0.0
     */
    public function getAllProductsCount()
    {
        $count = $this->productManagement->getCount();
        return (int)$count;
    }

    /**
     * Get count of configurable products
     *
     * @return int
     * @since 2.0.0
     */
    public function getConfigurableCount()
    {
        $count = $this->configurableManagement->getCount();
        return (int)$count;
    }

    /**
     * Get count of products which are active
     *
     * @return int
     * @since 2.0.0
     */
    public function getActiveCatalogSize()
    {
        $count = $this->productManagement->getCount(Status::STATUS_ENABLED);
        return (int)$count;
    }

    /**
     * Get count of categories, minus one which is the root category
     *
     * @return int
     * @since 2.0.0
     */
    public function getCategoryCount()
    {
        $count = $this->categoryManagement->getCount();
        return (int)$count;
    }

    /**
     * Get customer count
     *
     * @return int
     * @since 2.0.0
     */
    public function getCustomerCount()
    {
        $count = $this->customerManagement->getCount();
        return (int)$count;
    }

    /**
     * Get count of websites, minus one to exclude admin website
     *
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteCount()
    {
        $count = $this->websiteManagement->getCount();
        return (int)$count;
    }

    /**
     * Get count of store views
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreViewsCount()
    {
        $count = $this->storeManagement->getCount();
        return (int)$count;
    }
}
