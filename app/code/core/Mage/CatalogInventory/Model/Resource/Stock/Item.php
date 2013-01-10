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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Stock item resource model
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Resource_Stock_Item extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_item', 'item_id');
    }

    /**
     * Loading stock item data by product
     *
     * @param Mage_CatalogInventory_Model_Stock_Item $item
     * @param int $productId
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function loadByProductId(Mage_CatalogInventory_Model_Stock_Item $item, $productId)
    {
        $select = $this->_getLoadSelect('product_id', $productId, $item)
            ->where('stock_id = :stock_id');
        $data = $this->_getReadAdapter()->fetchRow($select, array(':stock_id' => $item->getStockId()));
        if ($data) {
            $item->setData($data);
        }
        $this->_afterLoad($item);
        return $this;
    }

    /**
     * Retrieve select object and join it to product entity table to get type ids
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_CatalogInventory_Model_Stock_Item $object
     * @return Varien_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object)
            ->join(array('p' => $this->getTable('catalog_product_entity')),
                'product_id=p.entity_id',
                array('type_id')
            );
        return $select;
    }

    /**
     * Add join for catalog in stock field to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $productCollection
     * @param array $columns
     * @return Mage_CatalogInventory_Model_Resource_Stock_Item
     */
    public function addCatalogInventoryToProductCollection($productCollection, $columns = null)
    {
        if ($columns === null) {
            $adapter = $this->_getReadAdapter();
            $isManageStock = (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
            $stockExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 1', $isManageStock, 'cisi.manage_stock');
            $stockExpr = $adapter->getCheckSql("({$stockExpr} = 1)", 'cisi.is_in_stock', '1');

            $columns = array(
                'is_saleable' => new Zend_Db_Expr($stockExpr),
                'inventory_in_stock' => 'is_in_stock'
            );
        }

        $productCollection->joinTable(
            array('cisi' => 'cataloginventory_stock_item'),
            'product_id=entity_id',
            $columns,
            null,
            'left'
        );
        return $this;
    }

    /**
     * Use qty correction for qty column update
     *
     * @param Mage_Core_Model_Abstract $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(Varien_Object $object, $table)
    {
        $data = parent::_prepareDataForTable($object, $table);
        if (!$object->isObjectNew() && $object->getQtyCorrection()) {
            $qty = abs($object->getQtyCorrection());
            if ($object->getQtyCorrection() < 0) {
                $data['qty'] = new Zend_Db_Expr('qty-' . $qty);
            } else {
                $data['qty'] = new Zend_Db_Expr('qty+' . $object->getQtyCorrection());
            }
        }
        return $data;
    }
}
