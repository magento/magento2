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
 * @since 2.2.0
 */
class CompositeProductRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * Calculated memory size for one record in catalog_product_index_price table
     */
    const MEMORY_SIZE_FOR_ONE_ROW = 200;

    /**
     * @var DefaultPrice
     * @since 2.2.0
     */
    private $indexerResource;

    /**
     * @var WebsiteManagementInterface
     * @since 2.2.0
     */
    private $websiteManagement;

    /**
     * @var CollectionFactory
     * @since 2.2.0
     */
    private $collectionFactory;

    /**
     * @param DefaultPrice $indexerResource
     * @param WebsiteManagementInterface $websiteManagement
     * @param CollectionFactory $collectionFactory
     * @since 2.2.0
     */
    public function __construct(
        DefaultPrice $indexerResource,
        WebsiteManagementInterface $websiteManagement,
        CollectionFactory $collectionFactory
    ) {
        $this->indexerResource = $indexerResource;
        $this->websiteManagement = $websiteManagement;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Calculate memory size for largest composite product in database.
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function estimateRowSize()
    {
        $websitesCount = $this->websiteManagement->getCount();
        $customerGroupCount = $this->collectionFactory->create()->getSize();

        $connection = $this->indexerResource->getConnection();
        $relationSelect = $connection->select();
        $relationSelect->from(
            ['relation' => $this->indexerResource->getTable('catalog_product_relation')],
            ['count' => new \Zend_Db_Expr('count(relation.child_id)')]
        );
        $relationSelect->group('parent_id');

        $maxSelect = $connection->select();
        $maxSelect->from(
            ['max_value' => $relationSelect],
            ['count' => new \Zend_Db_Expr('MAX(count)')]
        );
        $maxRelatedProductCount = $connection->fetchOne($maxSelect);

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
