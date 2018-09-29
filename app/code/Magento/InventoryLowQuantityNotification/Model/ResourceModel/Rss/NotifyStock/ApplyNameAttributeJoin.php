<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel\Rss\NotifyStock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add product attribute 'name' to select.
 */
class ApplyNameAttributeJoin
{
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
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->eavConfig = $eavConfig;
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
        $valueCondition = 'at_name.value';
        $tableName = $this->resourceConnection->getTableName('catalog_product_entity_varchar');

        if ($storeId != Store::DEFAULT_STORE_ID) {
            $select->join(
                ['at_name_default' => $tableName],
                $this->getConditionByAliasAndStoreId(Store::DEFAULT_STORE_ID, 'at_name_default'),
                []
            );
            $valueCondition = $connection->getCheckSql(
                'at_name.value_id > 0',
                'at_name.value',
                'at_name_default.value'
            );
        }

        $select->joinLeft(
            ['at_name' => $tableName],
            $this->getConditionByAliasAndStoreId((int)$storeId, 'at_name'),
            ['name' => $valueCondition]
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
        $attributeId = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::NAME)->getAttributeId();
        $connection = $this->resourceConnection->getConnection();

        return implode(
            [
                $alias . '.' . $linkField . ' = product.' . $linkField,
                $connection->prepareSqlCondition($alias . '.store_id', $storeId),
                $connection->prepareSqlCondition($alias . '.attribute_id', $attributeId),
            ],
            ' ' . Select::SQL_AND . ' '
        );
    }
}
