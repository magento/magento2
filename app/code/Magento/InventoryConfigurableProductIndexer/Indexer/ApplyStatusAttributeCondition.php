<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Indexer;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\EntityManager\MetadataPool;

class ApplyStatusAttributeCondition
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
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function execute(Select $select)
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $storeId = $this->storeManager->getStore()->getId();
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS);
        $attributeTableName = $attribute->getBackendTable();
        $attributeId = $attribute->getAttributeId();

        $connection = $this->resourceConnection->getConnection();

        $select->joinLeft(
            ['global_status_attr' => $attributeTableName],
            implode(
                [
                    'global_status_attr.' . $linkField . ' = product_entity.' . $linkField,
                    'global_status_attr.attribute_id = ' . $attributeId,
                    'global_status_attr.store_id = 0',
                ],
                ' ' . Select::SQL_AND . ' '
            ),
            []
        )->joinLeft(
            ['store_status_attr' => $attributeTableName],
            implode(
                [
                    'store_status_attr.' . $linkField . ' = product_entity.' . $linkField,
                    'store_status_attr.attribute_id = ' . $attributeId,
                    'store_status_attr.store_id = ' . $storeId,
                ],
                ' ' . Select::SQL_AND . ' '
            ),
            []
        );

        $condition = $connection->getCheckSql(
            $connection->prepareSqlCondition('store_status_attr.value_id', ['notnull' => true]),
            'store_status_attr.value',
            'global_status_attr.value'
        );
        $select->where($condition, 1);
    }
}
