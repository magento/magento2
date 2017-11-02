<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Query;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Query batch range iterator
 *
 * It is uses to processing selects which will obtain values from  $rangeField with relation one-to-many
 * This iterator make chunks with operator LIMIT...OFFSET,
 * starting with zero offset and finishing on OFFSET + LIMIT = TOTAL_COUNT
 *
 * @see \Magento\Framework\DB\Query\Generator
 * @see \Magento\Framework\DB\Query\BatchIteratorFactory
 * @see \Magento\Catalog\Model\Indexer\Category\Product\AbstractAction
 * @see \Magento\Framework\DB\Adapter\Pdo\Mysql
 */
class BatchRangeIterator implements BatchIteratorInterface
{
    /**
     * @var Select
     */
    private $currentSelect;

    /**
     * @var string|array
     */
    private $rangeField;

    /**
     * @var string
     * @deprecated unused class property
     */
    private $rangeFieldAlias;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $currentOffset = 0;

    /**
     * @var int
     */
    private $totalItemCount;

    /**
     * @var int
     */
    private $iteration = 0;

    /**
     * @var Select
     */
    private $select;

    /**
     * @var string
     */
    private $correlationName;

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
     * @param string|array $rangeField
     * @param string $rangeFieldAlias @deprecated
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
     * Return the current element
     *
     * If we don't have sub-select we should create and remember it.
     *
     * @return Select
     */
    public function current()
    {
        if (null === $this->currentSelect) {
            $this->isValid = ($this->currentOffset + $this->batchSize) <= $this->totalItemCount;
            $this->currentSelect = $this->initSelectObject();
        }
        return $this->currentSelect;
    }

    /**
     * Return the key of the current element
     *
     * Сan return the number of the current sub-select in the iteration.
     *
     * @return int
     */
    public function key()
    {
        return $this->iteration;
    }

    /**
     * Move forward to next sub-select
     *
     * Retrieve the next sub-select and move cursor to the next element.
     * Checks that the count of elements more than the sum of limit and offset.
     *
     * @return Select
     */
    public function next()
    {
        if (null === $this->currentSelect) {
            $this->current();
        }
        $this->isValid = ($this->batchSize + $this->currentOffset) <= $this->totalItemCount;
        $select = $this->initSelectObject();
        if ($this->isValid) {
            $this->iteration++;
            $this->currentSelect = $select;
        } else {
            $this->currentSelect = null;
        }
        return $this->currentSelect;
    }

    /**
     * Rewind the BatchRangeIterator to the first element.
     *
     * Allows to start iteration from the beginning.
     *
     * @return void
     */
    public function rewind()
    {
        $this->currentSelect = null;
        $this->iteration = 0;
        $this->isValid = true;
        $this->totalItemCount = 0;
    }

    /**
     * Checks if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * Initialize select object
     *
     * Return sub-select which is limited by current batch value and return items from n page of SQL request.
     *
     * @return \Magento\Framework\DB\Select
     */
    private function initSelectObject()
    {
        $object = clone $this->select;

        if (!$this->totalItemCount) {
            $wrapperSelect = $this->connection->select();
            $wrapperSelect->from(
                $object,
                [
                    new \Zend_Db_Expr('COUNT(*) as cnt')
                ]
            );
            $row = $this->connection->fetchRow($wrapperSelect);

            $this->totalItemCount = intval($row['cnt']);
        }

        $rangeField = is_array($this->rangeField) ? $this->rangeField : [$this->rangeField];

        /**
         * Reset sort order section from origin select object
         */
        foreach ($rangeField as $field) {
            $object->order($this->correlationName . '.' . $field . ' ' . \Magento\Framework\DB\Select::SQL_ASC);
        }
        $object->limit($this->batchSize, $this->currentOffset);
        $this->currentOffset += $this->batchSize;

        return $object;
    }
}
