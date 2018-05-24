<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\Query;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;

/**
 * Allows to join product attribute to Select. Used for build price index for specified dimension, w
 */
class JoinAttributeProcessor
{
    private $defaultStoreId;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    private $metadataPool;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * JoinProcessor constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Helper\Data $dataHelper
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        $connectionName = 'indexer'
    ) {
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->resource = $resource;
        $this->connectionName = $connectionName;
    }

    /**
     * @param Select $select
     * @param int $websiteId
     * @param string $attributeCode
     * @param string|null $attributeValue
     * @return \Zend_Db_Expr
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function process(Select $select, $websiteId, $attributeCode, $attributeValue = null)
    {
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        $attributeId = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $connection = $this->resource->getConnection($this->connectionName);
        $joinType = $attributeValue !== null ? 'join' : 'joinLeft';
        $productIdField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        if ($attribute->isScopeGlobal()) {
            //TODO: refactor global join - move outside if statement
            $alias = 'ta_' . $attributeCode;
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = e.{$productIdField} AND {$alias}.attribute_id = {$attributeId}" .
                " AND {$alias}.store_id = 0",
                []
            );
            $whereExpression = new \Zend_Db_Expr("{$alias}.value");
        } else {
            $storeId = $this->getDefaultStoreForWebsite($websiteId);
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
                " AND {$sAlias}.store_id = {$storeId}",
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

    /**
     * @param $websiteId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultStoreForWebsite($websiteId): int
    {
        if (!isset($this->defaultStoreId[$websiteId])) {
            $website = $this->storeManager->getWebsite($websiteId);
            $defaultGroup = $website->getDefaultGroup();
            $this->defaultStoreId[$websiteId] = (int) $defaultGroup->getDefaultStoreId();
        }

        return $this->defaultStoreId[$websiteId];
    }
}
