<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model\Resource;

/**
 * Bundle Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Bundle extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Relation
     */
    protected $_productRelation;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Model\Resource\Product\Relation $productRelation
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Catalog\Model\Resource\Product\Relation $productRelation
    ) {
        parent::__construct($resource);
        $this->_productRelation = $productRelation;
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
     * @return \Zend_DB_Select
     */
    protected function _getSelect($productId, $columns = [])
    {
        return $this->_getReadAdapter()->select()->from(
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
        return $this->_getReadAdapter()->fetchAll($this->_getSelect($productId, ["*"]));
    }

    /**
     * Removing all quote items for specified product
     *
     * @param int $productId
     * @return void
     */
    public function dropAllQuoteChildItems($productId)
    {
        $quoteItemIds = $this->_getReadAdapter()->fetchCol(
            $this->_getReadAdapter()->select()->from(
                $this->getTable('sales_quote_item'),
                ['item_id']
            )->where(
                'product_id = :product_id'
            ),
            ['product_id' => $productId]
        );

        if ($quoteItemIds) {
            $this->_getWriteAdapter()->delete(
                $this->getTable('sales_quote_item'),
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
        $this->_getWriteAdapter()->delete($this->getTable('catalog_product_bundle_selection'), $where);
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
}
