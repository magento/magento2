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

namespace Magento\CatalogInventory\Model\Resource;

/**
 * Stock resource model
 */
class Stock extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Is initialized configuration flag
     *
     * @var bool
     */
    protected $_isConfig;

    /**
     * Manage Stock flag
     *
     * @var bool
     */
    protected $_isConfigManageStock;

    /**
     * Backorders
     *
     * @var bool
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
     * @var \Magento\CatalogInventory\Model\Stock
     */
    protected $_stock;

    /**
     * @var \Magento\CatalogInventory\Service\V1\StockItemService
     */
    protected $stockItemService;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Stock model factory
     *
     * @var \Magento\CatalogInventory\Model\StockFactory
     */
    protected $_stockFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\CatalogInventory\Model\StockFactory $stockFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\CatalogInventory\Service\V1\StockItemService $stockItemService,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Model\StockFactory $stockFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        parent::__construct($resource);
        $this->stockItemService = $stockItemService;
        $this->_scopeConfig = $scopeConfig;
        $this->_stockFactory = $stockFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Define main table and initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cataloginventory_stock', 'stock_id');
    }

    /**
     * Lock product items
     *
     * @param \Magento\CatalogInventory\Model\Stock $stock
     * @param int|int[] $productIds
     * @return $this
     */
    public function lockProductItems($stock, $productIds)
    {
        $itemTable = $this->getTable('cataloginventory_stock_item');
        $select = $this->_getWriteAdapter()->select()->from($itemTable)
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
     * @param \Magento\CatalogInventory\Model\Stock $stock
     * @param int[] $productIds
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
        $select = $this->_getWriteAdapter()->select()->from(array('si' => $itemTable))
            ->join(array('p' => $productTable), 'p.entity_id=si.product_id', array('type_id'))
            ->where('stock_id=?', $stock->getId())
            ->where('product_id IN(?)', $productIds)
            ->forUpdate($lockRows);
        return $this->_getWriteAdapter()->fetchAll($select);
    }

    /**
     * Correct particular stock products qty based on operator
     *
     * @param \Magento\CatalogInventory\Model\Stock $stock
     * @param array $productQtys
     * @param string $operator +/-
     * @return $this
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
        $where = array('product_id IN (?)' => array_keys($productQtys), 'stock_id = ?' => $stock->getId());

        $adapter->beginTransaction();
        $adapter->update($this->getTable('cataloginventory_stock_item'), array('qty' => $value), $where);
        $adapter->commit();

        return $this;
    }

    /**
     * Add join to select only in stock products
     *
     * @param \Magento\Catalog\Model\Resource\Product\Link\Product\Collection $collection
     * @return $this
     */
    public function setInStockFilterToCollection($collection)
    {
        $manageStock = $this->_scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MANAGE_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $cond = array(
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=1 AND {{table}}.is_in_stock=1',
            '{{table}}.use_config_manage_stock = 0 AND {{table}}.manage_stock=0'
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
     * @return void
     */
    protected function _initConfig()
    {
        if (!$this->_isConfig) {
            $configMap = array(
                '_isConfigManageStock' => \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MANAGE_STOCK,
                '_isConfigBackorders' => \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_BACKORDERS,
                '_configMinQty' => \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_MIN_QTY,
                '_configNotifyStockQty' => \Magento\CatalogInventory\Model\Stock\Item::XML_PATH_NOTIFY_STOCK_QTY
            );

            foreach ($configMap as $field => $const) {
                $this->{$field} = (int) $this->_scopeConfig->getValue(
                    $const,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }

            $this->_isConfig = true;
            $this->_stock = $this->_stockFactory->create();
            $this->_configTypeIds = array_keys($this->stockItemService->getIsQtyTypeIds(true));
        }
    }

    /**
     * Set items out of stock basing on their quantities and config settings
     *
     * @return void
     */
    public function updateSetOutOfStock()
    {
        $this->_initConfig();
        $adapter = $this->_getWriteAdapter();
        $values = array('is_in_stock' => 0, 'stock_status_changed_auto' => 1);

        $select = $adapter->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf(
            'stock_id = %1$d' .
            ' AND is_in_stock = 1' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ((use_config_backorders = 1 AND %3$d = %4$d) OR (use_config_backorders = 0 AND backorders = %3$d))' .
            ' AND ((use_config_min_qty = 1 AND qty <= %5$d) OR (use_config_min_qty = 0 AND qty <= min_qty))' .
            ' AND product_id IN (%6$s)',
            $this->_stock->getId(),
            $this->_isConfigManageStock,
            \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO,
            $this->_isConfigBackorders,
            $this->_configMinQty,
            $select->assemble()
        );

        $adapter->update($this->getTable('cataloginventory_stock_item'), $values, $where);
    }

    /**
     * Set items in stock basing on their quantities and config settings
     *
     * @return void
     */
    public function updateSetInStock()
    {
        $this->_initConfig();
        $adapter = $this->_getWriteAdapter();
        $values = array('is_in_stock' => 1);

        $select = $adapter->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf(
            'stock_id = %1$d' .
            ' AND is_in_stock = 0' .
            ' AND stock_status_changed_auto = 1' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ((use_config_min_qty = 1 AND qty > %3$d) OR (use_config_min_qty = 0 AND qty > min_qty))' .
            ' AND product_id IN (%4$s)',
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
     * @return void
     */
    public function updateLowStockDate()
    {
        $this->_initConfig();

        $adapter = $this->_getWriteAdapter();
        $condition = $adapter->quoteInto(
            '(use_config_notify_stock_qty = 1 AND qty < ?)',
            $this->_configNotifyStockQty
        ) . ' OR (use_config_notify_stock_qty = 0 AND qty < notify_stock_qty)';
        $currentDbTime = $adapter->quoteInto('?', $this->dateTime->formatDate(true));
        $conditionalDate = $adapter->getCheckSql($condition, $currentDbTime, 'NULL');

        $value = array('low_stock_date' => new \Zend_Db_Expr($conditionalDate));

        $select = $adapter->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf(
            'stock_id = %1$d' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND product_id IN (%3$s)',
            $this->_stock->getId(),
            $this->_isConfigManageStock,
            $select->assemble()
        );

        $adapter->update($this->getTable('cataloginventory_stock_item'), $value, $where);
    }

    /**
     * Add low stock filter to product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @param array $fields
     * @return $this
     */
    public function addLowStockFilter(\Magento\Catalog\Model\Resource\Product\Collection $collection, $fields)
    {
        $this->_initConfig();
        $adapter = $collection->getSelect()->getAdapter();
        $qtyIf = $adapter->getCheckSql(
            'invtr.use_config_notify_stock_qty > 0',
            $this->_configNotifyStockQty,
            'invtr.notify_stock_qty'
        );
        $conditions = array(
            array(
                $adapter->prepareSqlCondition('invtr.use_config_manage_stock', 1),
                $adapter->prepareSqlCondition($this->_isConfigManageStock, 1),
                $adapter->prepareSqlCondition('invtr.qty', array('lt' => $qtyIf))
            ),
            array(
                $adapter->prepareSqlCondition('invtr.use_config_manage_stock', 0),
                $adapter->prepareSqlCondition('invtr.manage_stock', 1)
            )
        );

        $where = array();
        foreach ($conditions as $k => $part) {
            $where[$k] = join(' ' . \Zend_Db_Select::SQL_AND . ' ', $part);
        }

        $where = $adapter->prepareSqlCondition(
            'invtr.low_stock_date',
            array('notnull' => true)
        ) . ' ' . \Zend_Db_Select::SQL_AND . ' ((' . join(
            ') ' . \Zend_Db_Select::SQL_OR . ' (',
            $where
        ) . '))';

        $collection->joinTable(
            array('invtr' => 'cataloginventory_stock_item'),
            'product_id = entity_id',
            $fields,
            $where
        );
        return $this;
    }
}
