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

/**
 * Estimator of the EAV decimal index table row size.
 *
 * Estimates the amount of memory required to store the index data of the product
 * with the highest number of attributes/values.
 *
 * Can be used with batch size manager to ensure that the batch will be handled correctly by the database.
 * @see \Magento\Framework\Indexer\BatchSizeManagement
 * @since 2.2.0
 */
class DecimalRowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * @var Decimal
     * @since 2.2.0
     */
    private $indexerResource;

    /**
     * @var StoreManagementInterface
     * @since 2.2.0
     */
    private $storeManagement;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @param StoreManagementInterface $storeManagement
     * @param Decimal $indexerResource
     * @param MetadataPool $metadataPool
     * @since 2.2.0
     */
    public function __construct(
        StoreManagementInterface $storeManagement,
        Decimal $indexerResource,
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

        $valueSelect = $connection->select();
        $valueSelect->from(
            ['value_table' => $this->indexerResource->getTable('catalog_product_entity_decimal')],
            ['count' => new \Zend_Db_Expr('count(value_table.value_id)')]
        );
        $valueSelect->group([$entityIdField, 'store_id']);

        $maxSelect = $connection->select();
        $maxSelect->from(
            ['max_value' => $valueSelect],
            ['count' => new \Zend_Db_Expr('MAX(count)')]
        );
        $maxRowsPerStore = $connection->fetchOne($maxSelect);

        return ceil($maxRowsPerStore * $this->storeManagement->getCount() * 500);
    }
}
