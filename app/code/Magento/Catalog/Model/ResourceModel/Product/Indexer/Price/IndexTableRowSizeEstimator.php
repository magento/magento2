<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

/**
 * Estimate index memory size for simple product.
 * Size depends on websites and customer groups count.
 */
class IndexTableRowSizeEstimator implements \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface
{
    /**
     * Calculated memory size for one record in catalog_product_index_price table
     */
    const MEMORY_SIZE_FOR_ONE_ROW = 200;

    /**
     * @var \Magento\Store\Api\WebsiteManagementInterface
     */
    private $websiteManagement;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    private $collectionFactory;

    /**
     * CompositeProductBatchSizeCalculator constructor.
     * @param \Magento\Store\Api\WebsiteManagementInterface $websiteManagement
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Store\Api\WebsiteManagementInterface $websiteManagement,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory
    ) {
        $this->websiteManagement = $websiteManagement;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function estimateRowSize()
    {
        $websitesCount = $this->websiteManagement->getCount();

        /** @var \Magento\Customer\Model\ResourceModel\Group\Collection $collection */
        $collection = $this->collectionFactory->create();
        $customerGroupCount = $collection->getSize();

        /**
         * Calculate memory size for product in database.
         *
         * $websitesCount - active websites
         * $customerGroupCount - active customer groups
         * MEMORY_SIZE_FOR_ONE_ROW - calculated memory size for one record in catalog_product_index_price table
         */
        return ceil($websitesCount * $customerGroupCount * self::MEMORY_SIZE_FOR_ONE_ROW);
    }
}
