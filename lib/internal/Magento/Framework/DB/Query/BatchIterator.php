<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Query batch iterator
 */
class BatchIterator implements BatchIteratorInterface
{
    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var int
     */
    private $minValue = 0;

    /**
     * @var string
     */
    private $correlationName;

    /**
     * @var string
     */
    private $rangeField;

    /**
     * @var Select
     */
    private $currentSelect;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $iteration = 0;

    /**
     * @var string
     */
    private $rangeFieldAlias;

    /**
     * @var bool
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
     * Returns current select
     *
     * @return Select
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
     * Returns next select
     *
     * @return Select
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
     * Returns key
     *
     * @return int
     */
    public function key()
    {
        return $this->iteration;
    }

    /**
     * Returns is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * Rewind
     *
     * @return void
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
        // PgCompat: MAX() over zero matching rows is SQL NULL - when this batch is
        // empty (cnt below ends up 0 and the iterator stops right after this call
        // anyway), don't overwrite minValue with that null. A leftover null minValue
        // fed into initSelectObject()'s "> ?" bind is otherwise silently coerced to an
        // empty-string parameter by PDO, which MySQL's loose bigint/string comparison
        // tolerates (implicitly treating '' as 0) but Postgres rejects outright
        // ("invalid input syntax for type bigint"). Real-world trigger: any
        // FieldDataConverter::convert() call (e.g. Theme's ConvertSerializedData data
        // patch) whose target rows are exhausted after fewer than batchSize rows, or -
        // as first hit here - never existed at all.
        $this->minValue = $row['max'] ?? $this->minValue;
        return (int)$row['cnt'];
    }

    /**
     * Initialize select object.
     *
     * @return \Magento\Framework\DB\Select
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
