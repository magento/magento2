<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Catalog Product Indexer Abstract Resource Model
 *
 * @api
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractIndexer extends \Magento\Indexer\Model\ResourceModel\AbstractResource
{
    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool
     */
    protected $metadataPool;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        \Magento\Eav\Model\Config $eavConfig,
        $connectionName = null
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $tableStrategy, $connectionName);
    }

    /**
     * Retrieve catalog_product attribute instance by attribute code
     *
     * @param string $attributeCode
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected function _getAttribute($attributeCode)
    {
        return $this->_eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
    }

    /**
     * Add attribute join condition to select and return \Zend_Db_Expr
     * attribute value definition
     * If $condition is not empty apply limitation for select
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $attrCode              the attribute code
     * @param string|\Zend_Db_Expr $entity   the entity field or expression for condition
     * @param string|\Zend_Db_Expr $store    the store field or expression for condition
     * @param \Zend_Db_Expr $condition       the limitation condition
     * @param bool $required                if required or has condition used INNER join, else - LEFT
     * @return \Zend_Db_Expr                 the attribute value expression
     */
    protected function _addAttributeToSelect($select, $attrCode, $entity, $store, $condition = null, $required = false)
    {
        $attribute = $this->_getAttribute($attrCode);
        $attributeId = $attribute->getAttributeId();
        $attributeTable = $attribute->getBackend()->getTable();
        $connection = $this->getConnection();
        $joinType = $condition !== null || $required ? 'join' : 'joinLeft';
        $productIdField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();

        if ($attribute->isScopeGlobal()) {
            $alias = 'ta_' . $attrCode;
            $select->{$joinType}(
                [$alias => $attributeTable],
                "{$alias}.{$productIdField} = {$entity} AND {$alias}.attribute_id = {$attributeId}" .
                " AND {$alias}.store_id = 0",
                []
            );
            $expression = new \Zend_Db_Expr("{$alias}.value");
        } else {
            $dAlias = 'tad_' . $attrCode;
            $sAlias = 'tas_' . $attrCode;

            $select->{$joinType}(
                [$dAlias => $attributeTable],
                "{$dAlias}.{$productIdField} = {$entity} AND {$dAlias}.attribute_id = {$attributeId}" .
                " AND {$dAlias}.store_id = 0",
                []
            );
            $select->joinLeft(
                [$sAlias => $attributeTable],
                "{$sAlias}.{$productIdField} = {$entity} AND {$sAlias}.attribute_id = {$attributeId}" .
                " AND {$sAlias}.store_id = {$store}",
                []
            );
            $expression = $connection->getCheckSql(
                $connection->getIfNullSql("{$sAlias}.value_id", -1) . ' > 0',
                "{$sAlias}.value",
                "{$dAlias}.value"
            );
        }

        if ($condition !== null) {
            $select->where("{$expression}{$condition}");
        }

        return $expression;
    }

    /**
     * Add website data join to select
     * If add default store join also limitation of only has default store website
     * Joined table has aliases
     *  cw for website table,
     *  csg for store group table (joined by website default group)
     *  cs for store table (joined by website default store)
     *
     * @param \Magento\Framework\DB\Select $select the select object
     * @param bool $store add default store join
     * @param string|\Zend_Db_Expr $joinCondition the limitation for website_id
     * @return $this
     */
    protected function _addWebsiteJoinToSelect($select, $store = true, $joinCondition = null)
    {
        if ($joinCondition !== null) {
            $joinCondition = 'cw.website_id = ' . $joinCondition;
        }

        $select->join(['cw' => $this->getTable('store_website')], $joinCondition, []);

        if ($store) {
            $select->join(
                ['csg' => $this->getTable('store_group')],
                'csg.group_id = cw.default_group_id',
                []
            )->join(
                ['cs' => $this->getTable('store')],
                'cs.store_id = csg.default_store_id',
                []
            );
        }

        return $this;
    }

    /**
     * Add join for catalog/product_website table
     * Joined table has alias pw
     *
     * @param \Magento\Framework\DB\Select $select the select object
     * @param string|\Zend_Db_Expr $website the limitation of website_id
     * @param string|\Zend_Db_Expr $product the limitation of product_id
     * @return $this
     */
    protected function _addProductWebsiteJoinToSelect($select, $website, $product)
    {
        $select->join(
            ['pw' => $this->getTable('catalog_product_website')],
            "pw.product_id = {$product} AND pw.website_id = {$website}",
            []
        );

        return $this;
    }

    /**
     * Retrieve product relations by children
     *
     * @param int|array $childIds
     * @return array
     */
    public function getRelationsByChild($childIds)
    {
        $connection = $this->getConnection();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['relation' => $this->getTable('catalog_product_relation')],
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.' . $linkField . ' = relation.parent_id',
            ['e.entity_id']
        )->where(
            'relation.child_id IN(?)',
            $childIds
        );

        return array_map('intval', (array) $connection->fetchCol($select));
    }

    /**
     * Retrieve product relations by parents
     *
     * @param int|array $parentIds
     * @return array
     */
    public function getRelationsByParent($parentIds)
    {
        if (!is_array($parentIds)) {
            $parentIds = [$parentIds];
        }

        $result = [];
        if (!empty($parentIds)) {
            $connection = $this->getConnection();
            $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
            $select = $connection->select()->from(
                ['cpr' => $this->getTable('catalog_product_relation')],
                'child_id'
            )->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.' . $linkField . ' = cpr.parent_id'
            )->where(
                'e.entity_id IN(?)',
                $parentIds
            );
            $result = $connection->fetchCol($select);
        }

        return array_map('intval', $result);
    }

    /**
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    protected function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
