<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Query batch iterator
 * @since 2.1.3
 */
class BatchIterator implements BatchIteratorInterface
{
    /**
     * @var int
     * @since 2.1.3
     */
    private $batchSize;

    /**
     * @var Select
     * @since 2.1.3
     */
    private $select;

    /**
     * @var int
     * @since 2.1.3
     */
    private $minValue = 0;

    /**
     * @var string
     * @since 2.1.3
     */
    private $correlationName;

    /**
     * @var string
     * @since 2.1.3
     */
    private $rangeField;

    /**
     * @var Select
     * @since 2.1.3
     */
    private $currentSelect;

    /**
     * @var AdapterInterface
     * @since 2.1.3
     */
    private $connection;

    /**
     * @var int
     * @since 2.1.3
     */
    private $iteration = 0;

    /**
     * @var string
     * @since 2.1.3
     */
    private $rangeFieldAlias;

    /**
     * @var bool
     * @since 2.1.3
     */
    private $isValid = true;

    /**
     * Initialize dependencies.
     *
     * @param Select $select
     * @param int $batchSize
     * @param string $correlationName
     * @param string $rangeField
     * @param string $rangeFieldAlias
     * @since 2.1.3
     */
    public function __construct(
        Select $select,
        $batchSize,
        $correlationName,
        $rangeField,
        $rangeFieldAlias
    ) {
        $this->batchSize = $batchSize;
        $this->select = $select;
        $this->correlationName = $correlationName;
        $this->rangeField = $rangeField;
        $this->rangeFieldAlias = $rangeFieldAlias;
        $this->connection = $select->getConnection();
    }

    /**
     * @return Select
     * @since 2.1.3
     */
    public function current()
    {
        if (null == $this->currentSelect) {
            $this->currentSelect = $this->initSelectObject();
            $itemsCount = $this->calculateBatchSize($this->currentSelect);
            $this->isValid = $itemsCount > 0;
        }
        return $this->currentSelect;
    }

    /**
     * @return Select
     * @since 2.1.3
     */
    public function next()
    {
        if (null == $this->currentSelect) {
            $this->current();
        }
        $select = $this->initSelectObject();
        $itemsCountInSelect = $this->calculateBatchSize($select);
        $this->isValid = $itemsCountInSelect > 0;
        if ($this->isValid) {
            $this->iteration++;
            $this->currentSelect = $select;
        } else {
            $this->currentSelect = null;
        }
        return $this->currentSelect;
    }

    /**
     * @return int
     * @since 2.1.3
     */
    public function key()
    {
        return $this->iteration;
    }

    /**
     * @return bool
     * @since 2.1.3
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * @return void
     * @since 2.1.3
     */
    public function rewind()
    {
        $this->minValue = 0;
        $this->currentSelect = null;
        $this->iteration = 0;
        $this->isValid = true;
    }

    /**
     * Calculate batch size for select.
     *
     * @param Select $select
     * @return int
     * @since 2.1.3
     */
    private function calculateBatchSize(Select $select)
    {
        $wrapperSelect = $this->connection->select();
        $wrapperSelect->from(
            $select,
            [
                new \Zend_Db_Expr('MAX(' . $this->rangeFieldAlias . ') as max'),
                new \Zend_Db_Expr('COUNT(*) as cnt')
            ]
        );
        $row = $this->connection->fetchRow($wrapperSelect);
        $this->minValue = $row['max'];
        return intval($row['cnt']);
    }

    /**
     * Initialize select object.
     *
     * @return \Magento\Framework\DB\Select
     * @since 2.1.3
     */
    private function initSelectObject()
    {
        $object = clone $this->select;
        $object->where(
            $this->connection->quoteIdentifier($this->correlationName)
            . '.' . $this->connection->quoteIdentifier($this->rangeField)
            . ' > ?',
            $this->minValue
        );
        $object->limit($this->batchSize);
        /**
         * Reset sort order section from origin select object
         */
        $object->order($this->correlationName . '.' . $this->rangeField . ' ' . \Magento\Framework\DB\Select::SQL_ASC);
        return $object;
    }
}
