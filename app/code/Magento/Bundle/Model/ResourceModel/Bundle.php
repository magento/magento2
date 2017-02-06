<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\ResourceModel;

/**
 * Bundle Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Bundle extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation
     */
    protected $_productRelation;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    protected $quoteResource;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Relation $productRelation
     * @param \Magento\Quote\Model\ResourceModel\Quote $quoteResource
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Relation $productRelation,
        \Magento\Quote\Model\ResourceModel\Quote $quoteResource,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_productRelation = $productRelation;
        $this->quoteResource = $quoteResource;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Preparing select for getting selection's raw data by product id
     * also can be specified extra parameter for limit which columns should be selected
     *
     * @param int $productId
     * @param array $columns
     * @return \Magento\Framework\DB\Select
     */
    protected function _getSelect($productId, $columns = [])
    {
        return $this->getConnection()->select()->from(
            ["bundle_option" => $this->getTable('catalog_product_bundle_option')],
            ['type', 'option_id']
        )->where(
            "bundle_option.parent_id = ?",
            $productId
        )->where(
            "bundle_option.required = 1"
        )->joinLeft(
            ["bundle_selection" => $this->getTable('catalog_product_bundle_selection')],
            "bundle_selection.option_id = bundle_option.option_id",
            $columns
        );
    }

    /**
     * Retrieve selection data for specified product id
     *
     * @param int $productId
     * @return array
     */
    public function getSelectionsData($productId)
    {
        return $this->getConnection()->fetchAll($this->_getSelect($productId, ["*"]));
    }

    /**
     * Removing all quote items for specified product
     *
     * @param int $productId
     * @return void
     */
    public function dropAllQuoteChildItems($productId)
    {
        $connection = $this->quoteResource->getConnection();
        $select = $connection->select();
        $quoteItemIds = $connection->fetchCol(
            $select->from(
                $this->getTable('quote_item'),
                ['item_id']
            )->where(
                'product_id = :product_id'
            ),
            ['product_id' => $productId]
        );

        if ($quoteItemIds) {
            $connection->delete(
                $this->getTable('quote_item'),
                ['parent_item_id IN(?)' => $quoteItemIds]
            );
        }
    }

    /**
     * Removes specified selections by ids for specified product id
     *
     * @param int $productId
     * @param array $ids
     * @return void
     */
    public function dropAllUnneededSelections($productId, $ids)
    {
        $where = ['parent_product_id = ?' => $productId];
        if (!empty($ids)) {
            $where['selection_id NOT IN (?) '] = $ids;
        }
        $this->getConnection()->delete($this->getTable('catalog_product_bundle_selection'), $where);
    }

    /**
     * Save product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function saveProductRelations($parentId, $childIds)
    {
        $this->_productRelation->processRelations($parentId, $childIds);

        return $this;
    }

    /**
     * Add product relation (duplicate will be updated)
     *
     * @param int $parentId
     * @param int $childId
     * @return $this
     */
    public function addProductRelation($parentId, $childId)
    {
        $this->_productRelation->addRelation($parentId, $childId);
        return $this;
    }

    /**
     * Add product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function addProductRelations($parentId, $childIds)
    {
        $this->_productRelation->addRelations($parentId, $childIds);
        return $this;
    }

    /**
     * Remove product relations
     *
     * @param int $parentId
     * @param array $childIds
     * @return $this
     */
    public function removeProductRelations($parentId, $childIds)
    {
        $this->_productRelation->removeRelations($parentId, $childIds);
        return $this;
    }
}
