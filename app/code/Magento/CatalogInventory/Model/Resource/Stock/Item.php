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

namespace Magento\CatalogInventory\Model\Resource\Stock;

/**
 * Stock item resource model
 */
class Item extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($resource);
    }

    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock_item', 'item_id');
    }

    /**
     * Loading stock item data by product
     *
     * @param \Magento\CatalogInventory\Model\Stock\Item $item
     * @param int $productId
     * @return $this
     */
    public function loadByProductId(\Magento\CatalogInventory\Model\Stock\Item $item, $productId)
    {
        $select = $this->_getLoadSelect('product_id', $productId, $item)->where('stock_id = :stock_id');
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
     * @param int $value
     * @param \Magento\CatalogInventory\Model\Stock\Item $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object)
            ->join(array('p' => $this->getTable('catalog_product_entity')), 'product_id=p.entity_id', array('type_id'));
        return $select;
    }

    /**
     * Add join for catalog in stock field to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $productCollection
     * @param array $columns
     * @return $this
     */
    public function addCatalogInventoryToProductCollection($productCollection, $columns = null)
    {
        if ($columns === null) {
            $adapter = $this->_getReadAdapter();
            $isManageStock = (int) $this->_scopeConfig->getValue(
                \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MANAGE_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $stockExpr = $adapter->getCheckSql(
                'cisi.use_config_manage_stock = 1',
                $isManageStock,
                'cisi.manage_stock'
            );
            $stockExpr = $adapter->getCheckSql("({$stockExpr} = 1)", 'cisi.is_in_stock', '1');

            $columns = array('is_saleable' => new \Zend_Db_Expr($stockExpr), 'inventory_in_stock' => 'is_in_stock');
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
     * @param \Magento\Framework\Object $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(\Magento\Framework\Object $object, $table)
    {
        $data = parent::_prepareDataForTable($object, $table);
        $ifNullSql = $this->_getWriteAdapter()->getIfNullSql('qty');
        if (!$object->isObjectNew() && $object->getQtyCorrection()) {
            if ($object->getQty() === null) {
                $data['qty'] = null;
            } elseif ($object->getQtyCorrection() < 0) {
                $data['qty'] = new \Zend_Db_Expr($ifNullSql . '-' . abs($object->getQtyCorrection()));
            } else {
                $data['qty'] = new \Zend_Db_Expr($ifNullSql . '+' . $object->getQtyCorrection());
            }
        }
        return $data;
    }
}
