<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price;

class CompositeProductBatchSizeCalculator implements \Magento\Framework\Indexer\BatchSizeCalculatorInterface
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
    public function estimateBatchSize(\Magento\Framework\DB\Adapter\AdapterInterface $connection, $memoryTableMinRows)
    {
        // Override param, define 100000 records and change memory table size according the calculations
        $memoryTableMinRows = 100000;

        // Calculate memory table size for largest composite product
        $memoryForLargeComposite = $this->calculateMemorySize($connection);

        $maxHeapTableSize = $connection->fetchOne('SELECT @@max_heap_table_size;');
        $tmpTableSize = $connection->fetchOne('SELECT @@tmp_table_size;');
        $maxMemoryTableSize = min($maxHeapTableSize, $tmpTableSize);

        $size = (int) ($memoryForLargeComposite * $memoryTableMinRows);

        if ($maxMemoryTableSize < $size) {
            $connection->query('SET SESSION tmp_table_size = ' . $size);
            $connection->query('SET SESSION max_heap_table_size = ' . $size);
        }
        return $memoryTableMinRows;
    }

    /**
     * Calculate memory size for largest composite product in database.
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return float
     */
    private function calculateMemorySize(\Magento\Framework\DB\Adapter\AdapterInterface $connection)
    {
        $relationSelect = $connection->select();
        $relationSelect->from(
            ['relation' => $connection->getTableName('catalog_product_relation')],
            ['count' => new \Zend_Db_Expr('count(relation.child_id)')]
        );
        $relationSelect->group('parent_id');

        $maxSelect = $connection->select();
        $maxSelect->from(
            ['max_value' => $relationSelect],
            ['count' => new \Zend_Db_Expr('MAX(count)')]
        );

        $maxRelatedProductCount = $connection->fetchOne($maxSelect);
        $websitesCount = $this->websiteManagement->getCount();

        /** @var \Magento\Customer\Model\ResourceModel\Group\Collection $collection */
        $collection = $this->collectionFactory->create();
        $customerGroupCount = $collection->getSize();

        /**
         * Calculate memory size for largest composite product in database.
         *
         * $maxRelatedProductCount - maximum number of related products
         * $websitesCount - active websites
         * $customerGroupCount - active customer groups
         * 90 - calculated memory size for one record in catalog_product_index_price table
         */
        return ceil($maxRelatedProductCount * $websitesCount * $customerGroupCount * 90);
    }
}
