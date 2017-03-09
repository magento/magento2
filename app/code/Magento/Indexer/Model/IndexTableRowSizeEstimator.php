<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Model;

class IndexTableRowSizeEstimator implements \Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface
{
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
         * 90 - calculated memory size for one record in catalog_product_index_price table
         */
        return ceil($websitesCount * $customerGroupCount * 90);
    }
}
