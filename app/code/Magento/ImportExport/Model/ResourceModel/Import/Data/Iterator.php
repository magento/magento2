<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import\Data;

/**
 * Import data iterator
 */
class Iterator extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Ids of rows in table
     * @var array
     */
    protected $rowsIds = [];

    /**
     * Count of all rows in table
     * @var int
     */
    protected $rowsCount = 0;

    /**
     * Current iterator index
     * @var int
     */
    protected $currentIndex = 0;

    /**
     * Iterator constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);

        $this->rowsIds = $this->getRowsIds();
        $this->rowsCount = count($this->rowsIds);
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('importexport_importdata', 'id');
    }

    /**
     * Get all existing rows ids to use them later for efficient iteration
     * @return array
     */
    protected function getRowsIds() {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from('importexport_importdata', ['id'])
            ->order('id ASC');

        return $connection->fetchCol($select);
    }

    /**
     * Returns current row
     *
     * @return array
     */
    public function current()
    {
        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from('importexport_importdata', ['data'])
            ->order('id ASC')
            ->where('id = ?', $this->rowsIds[$this->currentIndex]);

        $statement = $connection->query($select);

        $row = $statement->fetch();

        return [$row['data']];
    }

    /**
     * Moves iterator to next row
     */
    public function next()
    {
        $this->currentIndex++;
    }

    /**
     * Gets current row key
     * @return int
     */
    public function key()
    {
        return $this->rowsIds[$this->currentIndex];
    }

    /**
     * Returns if current row is valid
     * @return int
     */
    public function valid()
    {
        return $this->currentIndex < $this->rowsCount;
    }

    /**
     * Rewinds iterator to first row
     */
    public function rewind()
    {
        $this->currentIndex = 0;
    }
}
