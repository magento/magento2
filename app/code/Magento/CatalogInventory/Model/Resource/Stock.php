<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Resource;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Stock resource model
 */
class Stock extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

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
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param StockConfigurationInterface $stockConfiguration
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        StockConfigurationInterface $stockConfiguration,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($resource);
        $this->_scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->stockConfiguration = $stockConfiguration;
        $this->storeManager = $storeManager;
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
     * Lock Stock Item records
     *
     * @param int[] $productIds
     * @param int $websiteId
     * @return array
     */
    public function lockProductsStock($productIds, $websiteId)
    {
        if (empty($productIds)) {
            return [];
        }
        $itemTable = $this->getTable('cataloginventory_stock_item');
        $productTable = $this->getTable('catalog_product_entity');
        $select = $this->_getWriteAdapter()->select()->from(['si' => $itemTable])
            ->join(['p' => $productTable], 'p.entity_id=si.product_id', ['type_id'])
            ->where('website_id=?', $websiteId)
            ->where('product_id IN(?)', $productIds)
            ->forUpdate(true);
        return $this->_getWriteAdapter()->fetchAll($select);
    }

    /**
     * Correct particular stock products qty based on operator
     *
     * @param array $items
     * @param int $websiteId
     * @param string $operator +/-
     * @return $this
     */
    public function correctItemsQty(array $items, $websiteId, $operator = '-')
    {
        if (empty($items)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $conditions = [];
        foreach ($items as $productId => $qty) {
            $case = $adapter->quoteInto('?', $productId);
            $result = $adapter->quoteInto("qty{$operator}?", $qty);
            $conditions[$case] = $result;
        }

        $value = $adapter->getCaseSql('product_id', $conditions, 'qty');
        $where = ['product_id IN (?)' => array_keys($items), 'website_id = ?' => $websiteId];

        $adapter->beginTransaction();
        $adapter->update($this->getTable('cataloginventory_stock_item'), ['qty' => $value], $where);
        $adapter->commit();

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
            $configMap = [
                '_isConfigManageStock' => \Magento\CatalogInventory\Model\Configuration::XML_PATH_MANAGE_STOCK,
                '_isConfigBackorders' => \Magento\CatalogInventory\Model\Configuration::XML_PATH_BACKORDERS,
                '_configMinQty' => \Magento\CatalogInventory\Model\Configuration::XML_PATH_MIN_QTY,
                '_configNotifyStockQty' => \Magento\CatalogInventory\Model\Configuration::XML_PATH_NOTIFY_STOCK_QTY,
            ];

            foreach ($configMap as $field => $const) {
                $this->{$field} = (int) $this->_scopeConfig->getValue(
                    $const,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            }

            $this->_isConfig = true;
            $this->_configTypeIds = array_keys($this->stockConfiguration->getIsQtyTypeIds(true));
        }
    }

    /**
     * Set items out of stock basing on their quantities and config settings
     *
     * @param string|int $website
     * @return void
     */
    public function updateSetOutOfStock($website = null)
    {
        $websiteId = $this->storeManager->getWebsite($website)->getId();
        $this->_initConfig();
        $adapter = $this->_getWriteAdapter();
        $values = ['is_in_stock' => 0, 'stock_status_changed_auto' => 1];

        $select = $adapter->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf(
            'website_id = %1$d' .
            ' AND is_in_stock = 1' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ((use_config_backorders = 1 AND %3$d = %4$d) OR (use_config_backorders = 0 AND backorders = %3$d))' .
            ' AND ((use_config_min_qty = 1 AND qty <= %5$d) OR (use_config_min_qty = 0 AND qty <= min_qty))' .
            ' AND product_id IN (%6$s)',
            $websiteId,
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
     * @param int|string $website
     * @return void
     */
    public function updateSetInStock($website)
    {
        $websiteId = $this->storeManager->getWebsite($website)->getId();
        $this->_initConfig();
        $adapter = $this->_getWriteAdapter();
        $values = ['is_in_stock' => 1];

        $select = $adapter->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf(
            'website_id = %1$d' .
            ' AND is_in_stock = 0' .
            ' AND stock_status_changed_auto = 1' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ((use_config_min_qty = 1 AND qty > %3$d) OR (use_config_min_qty = 0 AND qty > min_qty))' .
            ' AND product_id IN (%4$s)',
            $websiteId,
            $this->_isConfigManageStock,
            $this->_configMinQty,
            $select->assemble()
        );

        $adapter->update($this->getTable('cataloginventory_stock_item'), $values, $where);
    }

    /**
     * Update items low stock date basing on their quantities and config settings
     *
     * @param int|string $website
     * @return void
     */
    public function updateLowStockDate($website)
    {
        $websiteId = $this->storeManager->getWebsite($website)->getId();
        $this->_initConfig();

        $adapter = $this->_getWriteAdapter();
        $condition = $adapter->quoteInto(
            '(use_config_notify_stock_qty = 1 AND qty < ?)',
            $this->_configNotifyStockQty
        ) . ' OR (use_config_notify_stock_qty = 0 AND qty < notify_stock_qty)';
        $currentDbTime = $adapter->quoteInto('?', $this->dateTime->formatDate(true));
        $conditionalDate = $adapter->getCheckSql($condition, $currentDbTime, 'NULL');

        $value = ['low_stock_date' => new \Zend_Db_Expr($conditionalDate)];

        $select = $adapter->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds);

        $where = sprintf(
            'website_id = %1$d' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND product_id IN (%3$s)',
            $websiteId,
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
        $conditions = [
            [
                $adapter->prepareSqlCondition('invtr.use_config_manage_stock', 1),
                $adapter->prepareSqlCondition($this->_isConfigManageStock, 1),
                $adapter->prepareSqlCondition('invtr.qty', ['lt' => $qtyIf]),
            ],
            [
                $adapter->prepareSqlCondition('invtr.use_config_manage_stock', 0),
                $adapter->prepareSqlCondition('invtr.manage_stock', 1)
            ],
        ];

        $where = [];
        foreach ($conditions as $k => $part) {
            $where[$k] = join(' ' . \Zend_Db_Select::SQL_AND . ' ', $part);
        }

        $where = $adapter->prepareSqlCondition(
            'invtr.low_stock_date',
            ['notnull' => true]
        ) . ' ' . \Zend_Db_Select::SQL_AND . ' ((' . join(
            ') ' . \Zend_Db_Select::SQL_OR . ' (',
            $where
        ) . '))';

        $collection->joinTable(
            ['invtr' => 'cataloginventory_stock_item'],
            'product_id = entity_id',
            $fields,
            $where
        );
        return $this;
    }
}
