<?php
/**
 * CatalogInventory Configurable Products Stock Status Indexer Resource Model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Indexer\Stock;

/**
 * CatalogInventory Configurable Products Stock Status Indexer Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;

class Configurable extends \Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\DefaultStock
{
    /**
     * Get the select object for get stock status by configurable product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $connection = $this->getConnection();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $select = $connection->select()->from(['e' => $this->getTable('catalog_product_entity')], ['entity_id']);
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns(
            'cw.website_id'
        )->join(
            ['cis' => $this->getTable('cataloginventory_stock')],
            '',
            ['stock_id']
        )->joinLeft(
            ['cisi' => $this->getTable('cataloginventory_stock_item')],
            'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
            []
        )->joinLeft(
            ['l' => $this->getTable('catalog_product_super_link')],
            'l.parent_id = e.entity_id',
            []
        )->join(
            ['le' => $this->getTable('catalog_product_entity')],
            'le.entity_id = l.product_id',
            []
        )->joinLeft(
            ['i' => $idxTable],
            'i.product_id = l.product_id AND cw.website_id = i.website_id AND cis.stock_id = i.stock_id',
            []
        )->columns(
            ['qty' => new \Zend_Db_Expr('0')]
        )->where(
            'cw.website_id != 0'
        )->where(
            'e.type_id = ?',
            $this->getTypeId()
        )->group(
            ['e.entity_id', 'cw.website_id', 'cis.stock_id']
        );

        $psExpr = $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id');
        $psCond = $connection->quoteInto($psExpr . '=?', ProductStatus::STATUS_ENABLED);

        if ($this->_isManageStock()) {
            $statusExpr = $connection->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                1,
                'cisi.is_in_stock'
            );
        } else {
            $statusExpr = $connection->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                'cisi.is_in_stock',
                1
            );
        }

        $optExpr = $connection->getCheckSql("{$psCond} AND le.required_options = 0", 'i.stock_status', 0);
        $stockStatusExpr = $connection->getLeastSql(["MAX({$optExpr})", "MIN({$statusExpr})"]);

        $select->columns(['status' => $stockStatusExpr]);

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }
}
