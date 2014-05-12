<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Bundle\Model\Resource\Indexer;

/**
 * Bundle Stock Status Indexer Resource Model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Stock extends \Magento\CatalogInventory\Model\Resource\Indexer\Stock\DefaultStock
{
    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param int|array $entityIds
     * @return $this
     */
    public function reindexEntity($entityIds)
    {
        $this->_updateIndex($entityIds);

        return $this;
    }

    /**
     * Retrieve table name for temporary bundle option stock index
     *
     * @return string
     */
    protected function _getBundleOptionTable()
    {
        return $this->getTable('catalog_product_bundle_stock_index');
    }

    /**
     * Prepare stock status per Bundle options, website and stock
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return $this
     */
    protected function _prepareBundleOptionStockData($entityIds = null, $usePrimaryTable = false)
    {
        $this->_cleanBundleOptionStockData();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from(
            array('bo' => $this->getTable('catalog_product_bundle_option')),
            array('parent_id')
        );
        $this->_addWebsiteJoinToSelect($select, false);
        $status = new \Zend_Db_Expr(
            'MAX(' . $adapter->getCheckSql('e.required_options = 0', 'i.stock_status', '0') . ')'
        );
        $select->columns(
            'website_id',
            'cw'
        )->join(
            array('cis' => $this->getTable('cataloginventory_stock')),
            '',
            array('stock_id')
        )->joinLeft(
            array('bs' => $this->getTable('catalog_product_bundle_selection')),
            'bs.option_id = bo.option_id',
            array()
        )->joinLeft(
            array('i' => $idxTable),
            'i.product_id = bs.product_id AND i.website_id = cw.website_id AND i.stock_id = cis.stock_id',
            array()
        )->joinLeft(
            array('e' => $this->getTable('catalog_product_entity')),
            'e.entity_id = bs.product_id',
            array()
        )->where(
            'cw.website_id != 0'
        )->group(
            array('bo.parent_id', 'cw.website_id', 'cis.stock_id', 'bo.option_id')
        )->columns(
            array('option_id' => 'bo.option_id', 'status' => $status)
        );

        if (!is_null($entityIds)) {
            $select->where('bo.parent_id IN(?)', $entityIds);
        }

        // clone select for bundle product without required bundle options
        $selectNonRequired = clone $select;

        $select->where('bo.required = ?', 1);
        $selectNonRequired->where('bo.required = ?', 0)->having($status . ' = 1');
        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $adapter->query($query);

        $query = $selectNonRequired->insertFromSelect($this->_getBundleOptionTable());
        $adapter->query($query);

        return $this;
    }

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $this->_prepareBundleOptionStockData($entityIds, $usePrimaryTable);

        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()->from(
            array('e' => $this->getTable('catalog_product_entity')),
            array('entity_id')
        );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', 'e.entity_id');
        $select->columns(
            'cw.website_id'
        )->join(
            array('cis' => $this->getTable('cataloginventory_stock')),
            '',
            array('stock_id')
        )->joinLeft(
            array('cisi' => $this->getTable('cataloginventory_stock_item')),
            'cisi.stock_id = cis.stock_id AND cisi.product_id = e.entity_id',
            array()
        )->joinLeft(
            array('o' => $this->_getBundleOptionTable()),
            'o.entity_id = e.entity_id AND o.website_id = cw.website_id AND o.stock_id = cis.stock_id',
            array()
        )->columns(
            array('qty' => new \Zend_Db_Expr('0'))
        )->where(
            'cw.website_id != 0'
        )->where(
            'e.type_id = ?',
            $this->getTypeId()
        )->group(
            array('e.entity_id', 'cw.website_id', 'cis.stock_id')
        );

        // add limitation of status
        $condition = $adapter->quoteInto('=?', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $condition);

        if ($this->_isManageStock()) {
            $statusExpr = $adapter->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 0',
                '1',
                'cisi.is_in_stock'
            );
        } else {
            $statusExpr = $adapter->getCheckSql(
                'cisi.use_config_manage_stock = 0 AND cisi.manage_stock = 1',
                'cisi.is_in_stock',
                '1'
            );
        }

        $select->columns(
            array(
                'status' => $adapter->getLeastSql(
                    array(
                        new \Zend_Db_Expr(
                            'MIN(' . $adapter->getCheckSql('o.stock_status IS NOT NULL', 'o.stock_status', '0') . ')'
                        ),
                        new \Zend_Db_Expr('MIN(' . $statusExpr . ')')
                    )
                )
            )
        );

        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        return $select;
    }

    /**
     * Prepare stock status data in temporary index table
     *
     * @param int|array $entityIds  the product limitation
     * @return $this
     */
    protected function _prepareIndexTable($entityIds = null)
    {
        parent::_prepareIndexTable($entityIds);
        $this->_cleanBundleOptionStockData();

        return $this;
    }

    /**
     * Update Stock status index by product ids
     *
     * @param array|int $entityIds
     * @return $this
     */
    protected function _updateIndex($entityIds)
    {
        parent::_updateIndex($entityIds);
        $this->_cleanBundleOptionStockData();

        return $this;
    }

    /**
     * Clean temporary bundle options stock data
     *
     * @return $this
     */
    protected function _cleanBundleOptionStockData()
    {
        $this->_getWriteAdapter()->delete($this->_getBundleOptionTable());
        return $this;
    }
}
