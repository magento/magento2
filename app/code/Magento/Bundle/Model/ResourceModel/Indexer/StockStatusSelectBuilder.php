<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Framework\DB\Select;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

/**
 * Class StockStatusSelectBuilder
 * Is used to create Select object that is used for Bundle product stock status indexation
 *
 * @see \Magento\Bundle\Model\ResourceModel\Indexer\Stock::_getStockStatusSelect
 */
class StockStatusSelectBuilder
{

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param Select $select
     * @return Select
     * @throws \Exception
     */
    public function buildSelect(Select $select)
    {
        $select = clone $select;
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select->reset(
            Select::COLUMNS
        )->columns(
            ['e.entity_id', 'cis.website_id', 'cis.stock_id']
        )->joinLeft(
            ['o' => $this->resourceConnection->getTableName('catalog_product_bundle_stock_index')],
            'o.entity_id = e.entity_id AND o.website_id = cis.website_id AND o.stock_id = cis.stock_id',
            []
        )->joinInner(
            ['cpr' => $this->resourceConnection->getTableName('catalog_product_relation')],
            'e.' . $linkField . ' = cpr.parent_id',
            []
        )->columns(
            ['qty' => new \Zend_Db_Expr('0')]
        );

        if ($metadata->getIdentifierField() === $metadata->getLinkField()) {
            $select->joinInner(
                ['cpei' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
                'cpr.child_id = cpei.' . $linkField
                . ' AND cpei.attribute_id = ' . $this->getAttribute('status')->getId()
                . ' AND cpei.value = ' . ProductStatus::STATUS_ENABLED,
                []
            );
        } else {
            $select->joinInner(
                ['cpel' => $this->resourceConnection->getTableName('catalog_product_entity')],
                'cpel.entity_id = cpr.child_id',
                []
            )->joinInner(
                ['cpei' => $this->resourceConnection->getTableName('catalog_product_entity_int')],
                'cpel.'. $linkField . ' = cpei.' . $linkField
                . ' AND cpei.attribute_id = ' . $this->getAttribute('status')->getId()
                . ' AND cpei.value = ' . ProductStatus::STATUS_ENABLED,
                []
            );
        }

        return $select;
    }

    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    private function getAttribute($attributeCode)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
    }
}
