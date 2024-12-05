<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\SalesSequence\Model\Manager;

/**
 * Quote resource model
 */
class Quote extends AbstractDb
{
    /**
     * @var \Magento\SalesSequence\Model\Manager
     */
    protected $sequenceManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Snapshot $entitySnapshot
     * @param RelationComposite $entityRelationComposite
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Snapshot $entitySnapshot,
        RelationComposite $entityRelationComposite,
        Manager $sequenceManager,
        $connectionName = null
    ) {
        $this->_uniqueFields = [];
        parent::__construct($context, $entitySnapshot, $entityRelationComposite, $connectionName);
        $this->sequenceManager = $sequenceManager;
    }

    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('quote', 'entity_id');
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $storeIds = $object->getSharedStoreIds();

        if ($storeIds) {
            // The comparison the arrays [0] != ['*'] returns `true` in PHP >= 8 and `false` otherwise
            // This check emulates an old behavior (as it was in PHP 7)
            if ($storeIds !== ['*'] && $storeIds !== [0]) {
                $select->where('store_id IN (?)', $storeIds);
            }
        } else {
            /**
             * For empty result
             */
            $select->where('store_id < ?', 0);
        }

        return $select;
    }

    /**
     * Load quote data by customer identifier
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $customerId
     * @return $this
     */
    public function loadByCustomerId($quote, $customerId)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect(
            'customer_id',
            $customerId,
            $quote
        )->where(
            'is_active = ?',
            1
        )->order(
            'updated_at ' . \Magento\Framework\DB\Select::SQL_DESC
        )->limit(
            1
        );

        $data = $connection->fetchRow($select);

        if ($data) {
            $quote->setData($data);
            $quote->setOrigData();
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * Load only active quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $quoteId
     * @return $this
     */
    public function loadActive($quote, $quoteId)
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('entity_id', $quoteId, $quote)->where('is_active = ?', 1);

        $data = $connection->fetchRow($select);
        if ($data) {
            $quote->setData($data);
            $quote->setOrigData();
        }

        $this->_afterLoad($quote);

        return $this;
    }

    /**
     * Load quote data by identifier without store
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $quoteId
     * @return $this
     */
    public function loadByIdWithoutStore($quote, $quoteId)
    {
        $connection = $this->getConnection();
        if ($connection) {
            $select = parent::_getLoadSelect('entity_id', $quoteId, $quote);

            $data = $connection->fetchRow($select);

            if ($data) {
                $quote->setData($data);
                $quote->setOrigData();
            }
        }

        $this->_afterLoad($quote);
        return $this;
    }

    /**
     * Get reserved order id
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function getReservedOrderId($quote)
    {
        return $this->sequenceManager->getSequence(
            \Magento\Sales\Model\Order::ENTITY,
            $quote->getStoreId()
        )
        ->getNextValue();
    }

    /**
     * Mark quotes - that depend on catalog price rules - to be recollected on demand
     *
     * @return $this
     */
    public function markQuotesRecollectOnCatalogRules()
    {
        $tableQuote = $this->getTable('quote');
        $subSelect = $this->getConnection()->select()->from(
            ['t2' => $this->getTable('quote_item')],
            ['entity_id' => 'quote_id']
        )->from(
            ['t3' => $this->getTable('catalogrule_product_price')],
            []
        )->where(
            't2.product_id = t3.product_id'
        )->group(
            'quote_id'
        );

        $select = $this->getConnection()->select()->join(
            ['t2' => $subSelect],
            't1.entity_id = t2.entity_id',
            ['trigger_recollect' => new \Zend_Db_Expr('1')]
        );

        $updateQuery = $select->crossUpdateFromSelect(['t1' => $tableQuote]);

        $this->getConnection()->query($updateQuery);

        return $this;
    }

    /**
     * Subtract product from all quotes quantities
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function subtractProductFromQuotes($product)
    {
        $productId = (int)$product->getId();
        if (!$productId) {
            return $this;
        }
        $connection = $this->getConnection();
        $subSelect = $connection->select();
        $conditionCheck = $connection->quoteIdentifier('q.items_count') . " > 0";
        $conditionTrue = $connection->quoteIdentifier('q.items_count') . ' - 1';
        $ifSql = "IF (" . $conditionCheck . "," . $conditionTrue . ", 0)";

        $subSelect->from(
            false,
            [
                'items_qty' => new \Zend_Db_Expr(
                    $connection->quoteIdentifier('q.items_qty') . ' - ' . $connection->quoteIdentifier('qi.qty')
                ),
                'items_count' => new \Zend_Db_Expr($ifSql),
                'updated_at' => 'q.updated_at',
            ]
        )->join(
            ['qi' => $this->getTable('quote_item')],
            implode(
                ' AND ',
                [
                    'q.entity_id = qi.quote_id',
                    'qi.parent_item_id IS NULL',
                    $connection->quoteInto('qi.product_id = ?', $productId)
                ]
            ),
            []
        );

        $updateQuery = $connection->updateFromSelect($subSelect, ['q' => $this->getTable('quote')]);

        $connection->query($updateQuery);

        return $this;
    }

    /**
     * Subtract product from all quotes quantities
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @deprecated 101.0.3
     * @see \Magento\Quote\Model\ResourceModel\Quote::subtractProductFromQuotes
     *
     * @return $this
     */
    public function substractProductFromQuotes($product)
    {
        return $this->subtractProductFromQuotes($product);
    }

    /**
     * Mark recollect contain product(s) quotes
     *
     * @param array|int|\Zend_Db_Expr $productIds
     * @return $this
     */
    public function markQuotesRecollect($productIds)
    {
        $tableQuote = $this->getTable('quote');
        $tableItem = $this->getTable('quote_item');
        $subSelect = $this->getConnection()
            ->select()
            ->from(
                $tableItem,
                ['entity_id' => 'quote_id']
            )->where(
                'product_id IN ( ? )',
                $productIds
            )->group(
                'quote_id'
            );
        $select = $this->getConnection()
            ->select()
            ->join(
                ['t2' => $subSelect],
                't1.entity_id = t2.entity_id',
                [
                    'trigger_recollect' => new \Zend_Db_Expr('1'),
                    'updated_at' => 't1.updated_at',
                ]
            );
        $updateQuery = $select->crossUpdateFromSelect(['t1' => $tableQuote]);
        $this->getConnection()->query($updateQuery);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->isPreventSaving()) {
            return parent::save($object);
        }

        return $this;
    }

    /**
     * Quickly check if quote exists
     *
     * Uses direct DB query due to performance reasons
     *
     * @param int $quoteId
     * @return bool
     */
    public function isExists(int $quoteId): bool
    {
        $connection = $this->getConnection();
        $mainTable = $this->getMainTable();
        $idFieldName = $this->getIdFieldName();

        $field = $connection->quoteIdentifier(sprintf('%s.%s', $mainTable, $idFieldName));
        $select = $connection->select()
            ->from($mainTable, [$idFieldName])
            ->where($field . '=?', $quoteId);

        return (bool)$connection->fetchOne($select);
    }
}
