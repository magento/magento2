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
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Stock resource model
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Resource_Stock extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Is initialized configuration flag
     *
     * @var boolean
     */
    protected $_isConfig;

    /**
     * Manage Stock flag
     *
     * @var boolean
     */
    protected $_isConfigManageStock;

    /**
     * Backorders
     *
     * @var boolean
     */
    protected $_isConfigBackorders;

    /**
     * Minimum quantity allowed in shopping card
     *
     * @var int
     */
    protected $_configMinQty;

    /**
     * Product types that could have quantities
     *
     * @var array
     */
    protected $_configTypeIds;

    /**
     * Notify for quantity below _configNotifyStockQty value
     *
     * @var int
     */
    protected $_configNotifyStockQty;

    /**
     * Ctalog Inventory Stock instance
     *
     * @var Mage_CatalogInventory_Model_Stock
     */
    protected $_stock;

    /**
     * Define main table and initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock', 'stock_id');
    }

    /**
     * Lock product items
     *
     * @param Mage_CatalogInventory_Model_Stock $stock
     * @param int|array $productIds
     * @return Mage_CatalogInventory_Model_Resource_Stock
     */
    public function lockProductItems($stock, $productIds)
    {
        $itemTable = $this->getTable('cataloginventory_stock_item');
        $select = $this->_getWriteAdapter()->select()
            ->from($itemTable)
            ->where('stock_id=?', $stock->getId())
            ->where('product_id IN(?)', $productIds)
            ->forUpdate(true);
        /**
         * We use write adapter for resolving problems with replication
         */
        $this->_getWriteAdapter()->query($select);
        return $this;
    }

    /**
     * Get stock items data for requested products
     *
     * @param Mage_CatalogInventory_Model_Stock $stock
     * @param array $productIds
     * @param bool $lockRows
     * @return array
     */
    public function getProductsStock($stock, $productIds, $lockRows = false)
    {
        if (empty($productIds)) {
            return array();
        }
        $itemTable = $this->getTable('cataloginventory_stock_item');
        $productTable = $this->getTable('catalog_product_entity');
        $select = $this->_getWriteAdapter()->select()
            ->from(array('si' => $itemTable))
            ->join(array('p' => $productTable), 'p.entity_id=si.product_id', array('type_id'))
            ->where('stock_id=?', $stock->getId())
            ->where('product_id IN(?)', $productIds)
            ->forUpdate($lockRows);
        return $this->_getWriteAdapter()->fetchAll($select);
    }

    /**
     * Correct particular stock products qty based on operator
     *
     * @param Mage_CatalogInventory_Model_Stock $stock
     * @param array $productQtys
     * @param string $operator +/-
     * @return Mage_CatalogInventory_Model_Resource_Stock
     */
    public function correctItemsQty($stock, $productQtys, $operator = '-')
    {
        if (empty($productQtys)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $conditions = array();
        foreach ($productQtys as $productId => $qty) {
            $case = $adapter->quoteInto('?', $productId);
            $result = $adapter->quoteInto("qty{$operator}?", $qty);
            $conditions[$case] = $result;
        }

        $value = $adapter->getCaseSql('product_id', $conditions, 'qty');

        $where = array(
            'product_id IN (?)' => array_keys($productQtys),
            'stock_id = ?'      => $stock->getId()
        );

        $adapter->beginTransaction();
        $adapter->update($this->getTable('cataloginventory_stock_item'), array('qty' => $value), $where);
        $adapter->commit();

        return $this;
    }

    /**
     * add join to select only in stock products
     *
     * @param Mage_Catalog_Model_Resource_Product_Link_Product_Collection $collection
     * @return Mage_CatalogInventory_Model_Resource_Stock
     */
    public function setInStockFilterToCollection($collection)
    {
        $manageStock = Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $cond = array(
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0',
        );

        if ($manageStock) {
            $cond[] = '{{table}}.use_config_manage_stock = 1 AND {{table}}.is_in_stock=1';
        } else {
            $cond[] = '{{table}}.use_config_manage_stock = 1';
        }

        $collection->joinField(
            'inventory_in_stock',
            'cataloginventory_stock_item',
            'is_in_stock',
            'product_id=entity_id',
            '(' . join(') OR (', $cond) . ')'
        );
        return $this;
    }

    /**
     * Load some inventory configuration settings
     *
     */
    protected function _initConfig()
    {
        if (!$this->_isConfig) {
            $this->_isConfig = true;
            $this->_isConfigManageStock  = (int)Mage::getStoreConfigFlag(
                Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK
            );
            $this->_isConfigBackorders   = (int)Mage::getStoreConfig(
                Mage_CatalogInventory_Model_Stock_Item::XML_PATH_BACKORDERS
            );
            $this->_configMinQty         = (int)Mage::getStoreConfig(
                Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MIN_QTY
            );
            $this->_configNotifyStockQty = (int)Mage::getStoreConfig(
                Mage_CatalogInventory_Model_Stock_Item::XML_PATH_NOTIFY_STOCK_QTY
            );
            $this->_configTypeIds        = array_keys(
                Mage::helper('Mage_CatalogInventory_Helper_Data')->getIsQtyTypeIds(true)
            );
            $this->_stock                = Mage::getModel('Mage_CatalogInventory_Model_Stock');
        }
    }

    /**
     * Set items out of stock basing on their quantities and config settings
     *
     */
    public function updateSetOutOfStock()
    {
        $this->_initConfig();
        $adapter = $this->_getWriteAdapter();
        $values  = array(
            'is_in_stock'                  => 0,
            'stock_status_changed_auto'    => 1
        );

        $select = $adapter->select()
            ->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf('stock_id = %1$d'
            . ' AND is_in_stock = 1'
            . ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))'
            . ' AND ((use_config_backorders = 1 AND %3$d = %4$d) OR (use_config_backorders = 0 AND backorders = %3$d))'
            . ' AND ((use_config_min_qty = 1 AND qty <= %5$d) OR (use_config_min_qty = 0 AND qty <= min_qty))'
            . ' AND product_id IN (%6$s)',
            $this->_stock->getId(),
            $this->_isConfigManageStock,
            Mage_CatalogInventory_Model_Stock::BACKORDERS_NO,
            $this->_isConfigBackorders,
            $this->_configMinQty,
            $select->assemble()
        );

        $adapter->update($this->getTable('cataloginventory_stock_item'), $values, $where);
    }

    /**
     * Set items in stock basing on their quantities and config settings
     *
     */
    public function updateSetInStock()
    {
        $this->_initConfig();
        $adapter = $this->_getWriteAdapter();
        $values  = array(
            'is_in_stock'   => 1,
        );

        $select = $adapter->select()
            ->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf('stock_id = %1$d'
            . ' AND is_in_stock = 0'
            . ' AND stock_status_changed_auto = 1'
            . ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))'
            . ' AND ((use_config_min_qty = 1 AND qty > %3$d) OR (use_config_min_qty = 0 AND qty > min_qty))'
            . ' AND product_id IN (%4$s)',
            $this->_stock->getId(),
            $this->_isConfigManageStock,
            $this->_configMinQty,
            $select->assemble()
        );

        $adapter->update($this->getTable('cataloginventory_stock_item'), $values, $where);
    }

    /**
     * Update items low stock date basing on their quantities and config settings
     *
     */
    public function updateLowStockDate()
    {
        $this->_initConfig();

        $adapter = $this->_getWriteAdapter();
        $condition = $adapter->quoteInto('(use_config_notify_stock_qty = 1 AND qty < ?)',
            $this->_configNotifyStockQty) . ' OR (use_config_notify_stock_qty = 0 AND qty < notify_stock_qty)';
        $currentDbTime = $adapter->quoteInto('?', $this->formatDate(true));
        $conditionalDate = $adapter->getCheckSql($condition, $currentDbTime, 'NULL');

        $value  = array(
            'low_stock_date' => new Zend_Db_Expr($conditionalDate),
        );

        $select = $adapter->select()
            ->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf('stock_id = %1$d'
            . ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))'
            . ' AND product_id IN (%3$s)',
            $this->_stock->getId(),
            $this->_isConfigManageStock,
            $select->assemble()
        );

        $adapter->update($this->getTable('cataloginventory_stock_item'), $value, $where);
    }
}
