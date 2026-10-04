<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Estimator of the catalogrule_product_price index table row size.
 *
 * @see \Magento\Framework\Indexer\BatchSizeManagement
 */
class CatalogRuleProductPriceRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * Approximate size of catalogrule_product_price row in bytes
     * Based on table structure:
     * - rule_product_price_id: 4 bytes (int)
     * - rule_date: 3 bytes (date)
     * - customer_group_id: 4 bytes (int)
     * - product_id: 4 bytes (int)
     * - rule_price: 8 bytes (decimal)
     * - website_id: 2 bytes (smallint)
     * - latest_start_date: 3 bytes (date)
     * - earliest_end_date: 3 bytes (date)
     * Plus index overhead (~30%)
     */
    private const APPROXIMATE_ROW_SIZE_BYTES = 150;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CustomerGroupCollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param CustomerGroupCollectionFactory $customerGroupCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        CustomerGroupCollectionFactory $customerGroupCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function estimateRowSize()
    {
        $customerGroupCount = $this->customerGroupCollectionFactory->create()->getSize();

        $websiteCount = count($this->storeManager->getWebsites());

        $estimatedRowsPerProduct = $customerGroupCount * $websiteCount * 2;

        $memoryPerProduct = $estimatedRowsPerProduct * self::APPROXIMATE_ROW_SIZE_BYTES;

        return (int)ceil($memoryPerProduct);
    }
}
