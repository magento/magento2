<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav;

use Magento\Store\Api\StoreManagementInterface;
use Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Estimator of the EAV index table row size.
 *
 * Estimates the amount of memory required to store the index data of the product
 * with the highest number of attributes/values.
 *
 * Can be used with batch size manager to ensure that the batch will be handled correctly by the database.
 * @see \Magento\Framework\Indexer\BatchSizeManagement
 * @since 2.2.0
 */
class SourceRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * @var StoreManagementInterface
     * @since 2.2.0
     */
    private $storeManagement;

    /**
     * @var Source
     * @since 2.2.0
     */
    private $indexerResource;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @param StoreManagementInterface $storeManagement
     * @param Source $indexerResource
     * @param MetadataPool $metadataPool
     * @since 2.2.0
     */
    public function __construct(
        StoreManagementInterface $storeManagement,
        Source $indexerResource,
        MetadataPool $metadataPool
    ) {
        $this->storeManagement = $storeManagement;
        $this->indexerResource = $indexerResource;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function estimateRowSize()
    {
        $connection = $this->indexerResource->getConnection();
        $entityIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $maxRowsPerStore = max(
            $this->getMaxRowsPerStore(
                $connection,
                $this->indexerResource->getTable('catalog_product_entity_int'),
                $entityIdField
            ),
            $this->getMaxRowsPerStore(
                $connection,
                $this->indexerResource->getTable('catalog_product_entity_varchar'),
                $entityIdField
            )
        );

        return ceil($maxRowsPerStore * $this->storeManagement->getCount() * 500);
    }

    /**
     * Calculate maximum rows per store and product stored in the target table.
     *
     * @param AdapterInterface $connection
     * @param string $valueTable name of the target table
     * @param string $entityIdField entity ID field name
     * @return string maximum rows per store and product stored in the table
     * @since 2.2.0
     */
    private function getMaxRowsPerStore(
        AdapterInterface $connection,
        $valueTable,
        $entityIdField
    ) {
        $valueSelect = $connection->select();
        $valueSelect->from(
            ['value_table' => $valueTable],
            ['count' => new \Zend_Db_Expr('count(value_table.value_id)')]
        );
        $valueSelect->group([$entityIdField, 'store_id']);

        $maxSelect = $connection->select();
        $maxSelect->from(
            ['max_value' => $valueSelect],
            ['count' => new \Zend_Db_Expr('MAX(count)')]
        );
        return $connection->fetchOne($maxSelect);
    }
}
