<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;

/**
 * Allows to join product attribute to Select. Used for build price index for specified dimension
 */
class JoinAttributeProcessor
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Framework\App\ResourceConnection $resource,
        $connectionName = 'indexer'
    ) {
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->connectionName = $connectionName;
    }

    /**
     * @param Select $select
     * @param string $attributeCode
     * @param string|null $attributeValue
     * @return \Zend_Db_Expr
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function process(Select $select, $attributeCode, $attributeValue = null): \Zend_Db_Expr
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $attributeId = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $connection = $this->resource->getConnection($this->connectionName);
        $joinType = $attributeValue !== null ? 'join' : 'joinLeft';
        $productIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attributeCode;
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = e.{$productIdField} AND {$alias}.attribute_id = {$attributeId}" .
                " AND {$alias}.store_id = 0",
                []
            );
            $whereExpression = new Expression("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attributeCode;
            $sAlias = 'tas_' . $attributeCode;

            $select->{$joinType}(
                [$dAlias => $attributeTable],
                "{$dAlias}.{$productIdField} = e.{$productIdField} AND {$dAlias}.attribute_id = {$attributeId}" .
                " AND {$dAlias}.store_id = 0",
                []
            );
            $select->joinLeft(
                [$sAlias => $attributeTable],
                "{$sAlias}.{$productIdField} = e.{$productIdField} AND {$sAlias}.attribute_id = {$attributeId}" .
                " AND {$sAlias}.store_id = cwd.default_store_id",
                []
            );
            $whereExpression = $connection->getCheckSql(
                $connection->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        if ($attributeValue !== null) {
            $select->where("{$whereExpression} = ?", $attributeValue);
        }

        return $whereExpression;
    }
}
