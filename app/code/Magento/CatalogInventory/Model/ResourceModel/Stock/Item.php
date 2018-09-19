<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Stock item resource model
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Whether index events should be processed immediately
     *
     * @var bool
     */
    protected $processIndexEvents = true;

    /**
     * @var Processor
     */
    protected $stockIndexerProcessor;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param Context $context
     * @param Processor $processor
     * @param string $connectionName
     * @param StockConfigurationInterface $stockConfiguration
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        Processor $processor,
        $connectionName = null,
        StockConfigurationInterface $stockConfiguration = null,
        DateTime $dateTime = null
    ) {
        $this->stockIndexerProcessor = $processor;
        parent::__construct($context, $connectionName);

        $this->stockConfiguration = $stockConfiguration ??
            ObjectManager::getInstance()->get(StockConfigurationInterface::class);
        $this->dateTime = $dateTime ??
            ObjectManager::getInstance()->get(DateTime::class);
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
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $item
     * @param int $productId
     * @param int $stockId
     * @return $this
     */
    public function loadByProductId(\Magento\CatalogInventory\Api\Data\StockItemInterface $item, $productId, $stockId)
    {
        $select = $this->_getLoadSelect('product_id', $productId, $item)->where('stock_id = :stock_id');
        $data = $this->getConnection()->fetchRow($select, [':stock_id' => $stockId]);
        if ($data) {
            $item->setData($data);
        } else {
            // see \Magento\CatalogInventory\Model\Stock\Item::getStockQty
            $item->setStockQty(0);
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
            ->join(['p' => $this->getTable('catalog_product_entity')], 'product_id=p.entity_id', ['type_id']);
        return $select;
    }

    /**
     * Use qty correction for qty column update
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $table
     * @return array
     */
    protected function _prepareDataForTable(\Magento\Framework\DataObject $object, $table)
    {
        $data = parent::_prepareDataForTable($object, $table);
        $ifNullSql = $this->getConnection()->getIfNullSql('qty');
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

    /**
     * Reindex CatalogInventory save event
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);
        /** @var StockItemInterface $object */
        if ($this->processIndexEvents) {
            $this->stockIndexerProcessor->reindexRow($object->getProductId());
        }
        return $this;
    }

    /**
     * Set whether index events should be processed immediately
     *
     * @param bool $process
     * @return $this
     */
    public function setProcessIndexEvents($process = true)
    {
        $this->processIndexEvents = $process;
        return $this;
    }

    /**
     * Set items out of stock basing on their quantities and config settings
     *
     * @param int $websiteId
     * @return void
     */
    public function updateSetOutOfStock(int $websiteId)
    {
        $connection = $this->getConnection();

        $values = [
            'is_in_stock' => Stock::STOCK_OUT_OF_STOCK,
            'stock_status_changed_auto' => 1,
        ];
        $select = $this->buildProductsSelectByConfigTypes();
        $where = [
            'website_id = ' . $websiteId,
            'is_in_stock = ' . Stock::STOCK_IN_STOCK,
            '(use_config_manage_stock = 1 AND 1 = ' . $this->stockConfiguration->getManageStock() . ')'
            . ' OR (use_config_manage_stock = 0 AND manage_stock = 1)',
            '(use_config_min_qty = 1 AND qty <= ' . $this->stockConfiguration->getMinQty() . ')'
            . ' OR (use_config_min_qty = 0 AND qty <= min_qty)',
            'product_id IN (' . $select->assemble() . ')',
        ];
        $backordersWhere = '(use_config_backorders = 0 AND backorders = ' . Stock::BACKORDERS_NO . ')';
        if (Stock::BACKORDERS_NO == $this->stockConfiguration->getBackorders()) {
            $where[] = $backordersWhere . ' OR use_config_backorders = 1';
        } else {
            $where[] = $backordersWhere;
        }
        $connection->update($this->getMainTable(), $values, $where);

        $this->stockIndexerProcessor->markIndexerAsInvalid();
    }

    /**
     * Set items in stock basing on their quantities and config settings
     *
     * @param int $websiteId
     * @return void
     */
    public function updateSetInStock(int $websiteId)
    {
        $connection = $this->getConnection();

        $values = [
            'is_in_stock' => Stock::STOCK_IN_STOCK,
        ];
        $select = $this->buildProductsSelectByConfigTypes();
        $where = [
            'website_id = ' . $websiteId,
            'stock_status_changed_auto = 1',
            '(use_config_min_qty = 1 AND qty > ' . $this->stockConfiguration->getMinQty() . ')'
            . ' OR (use_config_min_qty = 0 AND qty > min_qty)',
            'product_id IN (' . $select->assemble() . ')',
        ];
        $manageStockWhere = '(use_config_manage_stock = 0 AND manage_stock = 1)';
        if ($this->stockConfiguration->getManageStock()) {
            $where[] = $manageStockWhere . ' OR use_config_manage_stock = 1';
        } else {
            $where[] = $manageStockWhere;
        }
        $connection->update($this->getMainTable(), $values, $where);

        $this->stockIndexerProcessor->markIndexerAsInvalid();
    }

    /**
     * Update items low stock date basing on their quantities and config settings
     *
     * @param int $websiteId
     * @return void
     */
    public function updateLowStockDate(int $websiteId)
    {
        $connection = $this->getConnection();

        $condition = $connection->quoteInto(
            '(use_config_notify_stock_qty = 1 AND qty < ?)',
            $this->stockConfiguration->getNotifyStockQty()
        ) . ' OR (use_config_notify_stock_qty = 0 AND qty < notify_stock_qty)';
        $currentDbTime = $connection->quoteInto('?', $this->dateTime->gmtDate());
        $conditionalDate = $connection->getCheckSql($condition, $currentDbTime, 'NULL');
        $value = [
            'low_stock_date' => new \Zend_Db_Expr($conditionalDate),
        ];
        $select = $this->buildProductsSelectByConfigTypes();
        $where = [
            'website_id = ' . $websiteId,
            'product_id IN (' . $select->assemble() . ')'
        ];
        $manageStockWhere = '(use_config_manage_stock = 0 AND manage_stock = 1)';
        if ($this->stockConfiguration->getManageStock()) {
            $where[] = $manageStockWhere . ' OR use_config_manage_stock = 1';
        } else {
            $where[] = $manageStockWhere;
        }
        $connection->update($this->getMainTable(), $value, $where);
    }

    public function getManageStockExpr(string $tableAlias = ''): \Zend_Db_Expr
    {
        if ($tableAlias) {
            $tableAlias .= '.';
        }
        $manageStock = $this->getConnection()->getCheckSql(
            $tableAlias . 'use_config_manage_stock = 1',
            $this->stockConfiguration->getManageStock(),
            $tableAlias . 'manage_stock'
        );

        return $manageStock;
    }

    public function getBackordersExpr(string $tableAlias = ''): \Zend_Db_Expr
    {
        if ($tableAlias) {
            $tableAlias .= '.';
        }
        $itemBackorders = $this->getConnection()->getCheckSql(
            $tableAlias . 'use_config_backorders = 1',
            $this->stockConfiguration->getBackorders(),
            $tableAlias . 'backorders'
        );

        return $itemBackorders;
    }

    public function getMinSaleQtyExpr(string $tableAlias = ''): \Zend_Db_Expr
    {
        if ($tableAlias) {
            $tableAlias .= '.';
        }
        $itemMinSaleQty = $this->getConnection()->getCheckSql(
            $tableAlias . 'use_config_min_sale_qty = 1',
            $this->stockConfiguration->getMinSaleQty(),
            $tableAlias . 'min_sale_qty'
        );

        return $itemMinSaleQty;
    }

    /**
     * Build select for products with types from config
     *
     * @return Select
     */
    private function buildProductsSelectByConfigTypes(): Select
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN (?)', array_keys($this->stockConfiguration->getIsQtyTypeIds(true)));

        return $select;
    }
}
