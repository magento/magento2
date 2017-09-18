<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor;
use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;

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
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Processor $processor
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Processor $processor,
        $connectionName = null
    ) {
        $this->stockIndexerProcessor = $processor;
        parent::__construct($context, $connectionName);
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
}
