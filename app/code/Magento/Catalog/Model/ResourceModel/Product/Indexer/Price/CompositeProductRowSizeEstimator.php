<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

use Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Estimate index memory size for largest composite product in catalog.
 */
class CompositeProductRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * Calculated memory size for one record in catalog_product_index_price table
     */
    const MEMORY_SIZE_FOR_ONE_ROW = 200;

    /**
     * @var WebsiteManagementInterface
     */
    private $websiteManagement;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CompositeProductRelationsCalculator
     * @since 2.2.0
     */
    private $compositeProductRelationsCalculator;

    /**
     * @param WebsiteManagementInterface $websiteManagement
     * @param CollectionFactory $collectionFactory
     * @param CompositeProductRelationsCalculator $compositeProductRelationsCalculator
     * @since 2.2.0
     */
    public function __construct(
        WebsiteManagementInterface $websiteManagement,
        CollectionFactory $collectionFactory,
        CompositeProductRelationsCalculator $compositeProductRelationsCalculator
    ) {
        $this->websiteManagement = $websiteManagement;
        $this->collectionFactory = $collectionFactory;
        $this->compositeProductRelationsCalculator = $compositeProductRelationsCalculator;
    }

    /**
     * Calculate memory size for largest composite product in database.
     *
     * {@inheritdoc}
     */
    public function estimateRowSize()
    {
        $websitesCount = $this->websiteManagement->getCount();
        $customerGroupCount = $this->collectionFactory->create()->getSize();
        $maxRelatedProductCount = $this->compositeProductRelationsCalculator->getMaxRelationsCount();

        /**
         * Calculate memory size for largest composite product in database.
         *
         * $maxRelatedProductCount - maximum number of related products
         * $websitesCount - active websites
         * $customerGroupCount - active customer groups
         * MEMORY_SIZE_FOR_ONE_ROW - calculated memory size for one record in catalog_product_index_price table
         */
        return ceil($maxRelatedProductCount * $websitesCount * $customerGroupCount * self::MEMORY_SIZE_FOR_ONE_ROW);
    }
}
