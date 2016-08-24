<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Query;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Query batch iterator
 */
class BatchIterator implements \Iterator
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
     * @return Select
     */
    public function current()
    {
        return $this->currentSelect;
    }

    /**
     * @return Select
     */
    public function next()
    {
        $this->iteration++;
        return $this->currentSelect;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->iteration;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $this->currentSelect = $this->initSelectObject();
        $batchSize = $this->calculateBatchSize($this->currentSelect);
        return $batchSize > 0;
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->minValue = 0;
        $this->iteration = 0;
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
        $this->minValue = $row['max'];
        return intval($row['cnt']);
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
        $object->order($this->correlationName . '.' . $this->rangeField . ' asc');
        return $object;
    }
}
