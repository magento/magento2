<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add product attribute 'status' to select.
 */
class ApplyStatusAttributeJoin
{
    /**
     * @var Status
     */
    private $productStatus;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Status $productStatus
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Status $productStatus,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->productStatus = $productStatus;
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param Select $select
     *
     * @return void
     */
    public function execute(Select $select)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $connection = $this->resourceConnection->getConnection();
        $valueCondition = 'at_status.value';
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_int');

        if ($storeId != Store::DEFAULT_STORE_ID) {
            $select->join(
                ['at_status_default' => $tableName],
                $this->getConditionByAliasAndStoreId(Store::DEFAULT_STORE_ID, 'at_status_default'),
                []
            );
            $valueCondition = $connection->getCheckSql(
                'at_status.value_id > 0',
                'at_status.value',
                'at_status_default.value'
            );
        }

        $select->joinLeft(
            ['at_status' => $tableName],
            $this->getConditionByAliasAndStoreId((int)$storeId, 'at_status'),
            ['status' => $valueCondition]
        );
    }

    /**
     * @param int $storeId
     * @param string $alias
     *
     * @return string
     */
    private function getConditionByAliasAndStoreId(int $storeId, string $alias): string
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS)->getAttributeId();
        $connection = $this->resourceConnection->getConnection();
        $statusVisibilityCondition = $connection->prepareSqlCondition(
            $alias . '.value',
            ['in' => $this->productStatus->getVisibleStatusIds()]
        );

        return implode(
            [
                $alias . '.' . $linkField . ' = product.' . $linkField,
                $statusVisibilityCondition,
                $connection->prepareSqlCondition($alias . '.store_id', $storeId),
                $connection->prepareSqlCondition($alias . '.attribute_id', $attributeId),
            ],
            ' ' . Select::SQL_AND . ' '
        );
    }
}
