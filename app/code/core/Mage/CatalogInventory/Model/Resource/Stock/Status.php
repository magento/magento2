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
 * CatalogInventory Stock Status per website Resource Model
 *
 * @category    Mage
 * @package     Mage_CatalogInventory
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogInventory_Model_Resource_Stock_Status extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_status', 'product_id');
    }

    /**
     * Save Product Status per website
     *
     * @param Mage_CatalogInventory_Model_Stock_Status $object
     * @param int $productId
     * @param int $status
     * @param float $qty
     * @param int $stockId
     * @param int|null $websiteId
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function saveProductStatus(Mage_CatalogInventory_Model_Stock_Status $object, $productId, $status, $qty = 0,
        $stockId = 1, $websiteId = null)
    {
        $websites = array_keys($object->getWebsites($websiteId));
        $adapter = $this->_getWriteAdapter();
        foreach ($websites as $websiteId) {
            $select = $adapter->select()
                ->from($this->getMainTable())
                ->where('product_id = :product_id')
                ->where('website_id = :website_id')
                ->where('stock_id = :stock_id');
            $bind = array(
                ':product_id' => $productId,
                ':website_id' => $websiteId,
                ':stock_id'   => $stockId
            );
            if ($row = $adapter->fetchRow($select, $bind)) {
                $bind = array(
                    'qty'           => $qty,
                    'stock_status'  => $status
                );
                $where = array(
                    $adapter->quoteInto('product_id=?', (int)$row['product_id']),
                    $adapter->quoteInto('website_id=?', (int)$row['website_id']),
                    $adapter->quoteInto('stock_id=?', (int)$row['stock_id']),
                );
                $adapter->update($this->getMainTable(), $bind, $where);
            } else {
                $bind = array(
                    'product_id'    => $productId,
                    'website_id'    => $websiteId,
                    'stock_id'      => $stockId,
                    'qty'           => $qty,
                    'stock_status'  => $status
                );
                $adapter->insert($this->getMainTable(), $bind);
            }
        }

        return $this;
    }

    /**
     * Retrieve product status
     * Return array as key product id, value - stock status
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductStatus($productIds, $websiteId, $stockId = 1)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('product_id', 'stock_status'))
            ->where('product_id IN(?)', $productIds)
            ->where('stock_id=?', (int)$stockId)
            ->where('website_id=?', (int)$websiteId);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve product(s) data array
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
     * @return array
     */
    public function getProductData($productIds, $websiteId, $stockId = 1)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $result = array();

        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->where('product_id IN(?)', $productIds)
            ->where('stock_id=?', (int)$stockId)
            ->where('website_id=?', (int)$websiteId);
        $result = $this->_getReadAdapter()->fetchAssoc($select);
        return $result;
    }

    /**
     * Retrieve websites and default stores
     * Return array as key website_id, value store_id
     *
     * @return array
     */
    public function getWebsiteStores()
    {
        $select = Mage::getModel('Mage_Core_Model_Website')->getDefaultStoresSelect(false);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve Product Type
     *
     * @param array|int $productIds
     * @return array
     */
    public function getProductsType($productIds)
    {
        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog_product_entity')),
                array('entity_id', 'type_id')
            )
            ->where('entity_id IN(?)', $productIds);
        return $this->_getReadAdapter()->fetchPairs($select);
    }

    /**
     * Retrieve Product part Collection array
     * Return array as key product id, value product type
     *
     * @param int $lastEntityId
     * @param int $limit
     * @return array
     */
    public function getProductCollection($lastEntityId = 0, $limit = 1000)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('e' => $this->getTable('catalog_product_entity')),
                array('entity_id', 'type_id')
            )
            ->order('entity_id ASC')
            ->where('entity_id > :entity_id')
            ->limit($limit);
        return $this->_getReadAdapter()->fetchPairs($select, array(':entity_id' => $lastEntityId));
    }

    /**
     * Add stock status to prepare index select
     *
     * @param Varien_Db_Select $select
     * @param Mage_Core_Model_Website $website
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function addStockStatusToSelect(Varien_Db_Select $select, Mage_Core_Model_Website $website)
    {
        $websiteId = $website->getId();
        $select->joinLeft(
            array('stock_status' => $this->getMainTable()),
            'e.entity_id = stock_status.product_id AND stock_status.website_id='.$websiteId,
            array('salable' => 'stock_status.stock_status')
        );

        return $this;
    }

    /**
     * Add stock status limitation to catalog product price index select object
     *
     * @param Varien_Db_Select $select
     * @param string|Zend_Db_Expr $entityField
     * @param string|Zend_Db_Expr $websiteField
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function prepareCatalogProductIndexSelect(Varien_Db_Select $select, $entityField, $websiteField)
    {
        $select->join(
            array('ciss' => $this->getMainTable()),
            "ciss.product_id = {$entityField} AND ciss.website_id = {$websiteField}",
            array()
        );
        $select->where('ciss.stock_status = ?', Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);

        return $this;
    }

    /**
     * Add only is in stock products filter to product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function addIsInStockFilterToCollection($collection)
    {
        $websiteId = Mage::app()->getStore($collection->getStoreId())->getWebsiteId();
        $joinCondition = $this->_getReadAdapter()
            ->quoteInto('e.entity_id = stock_status_index.product_id'
                . ' AND stock_status_index.website_id = ?', $websiteId
            );

        $joinCondition .= $this->_getReadAdapter()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID
        );

        $collection->getSelect()
            ->join(
                array('stock_status_index' => $this->getMainTable()),
                $joinCondition,
                array()
            )
            ->where('stock_status_index.stock_status=?', Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);

        return $this;
    }
}
