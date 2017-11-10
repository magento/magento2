<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel\Import\Data;

/**
 * DataProvider for import data
 */
class DataProvider extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Ids of rows in table
     * @var array
     */
    protected $rowsIds = [];

    /**
     * DataProvider constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('importexport_importdata', 'id');
    }

    /**
     * Get all existing rows ids to use them later for efficient data retrieval
     * @return array
     */
    protected function getRowsIds()
    {
        if(empty($this->rowsIds)) {
            $connection = $this->getConnection();

            $select = $connection
                ->select()
                ->from($this->getMainTable(), ['id'])
                ->order('id ASC');

            $this->rowsIds = $connection->fetchCol($select);
        }

        return $this->rowsIds;
    }

    /**
     * Returns import data row by $index
     *
     * @param $index int
     * @return array
     */
    public function getImportDataRow($index)
    {
        $rowsIds = $this->getRowsIds();

        if(!isset($rowsIds[$index])) {
            return null;
        }

        $connection = $this->getConnection();

        $select = $connection
            ->select()
            ->from($this->getMainTable(), ['data'])
            ->order('id ASC')
            ->where('id = ?', $rowsIds[$index]);

        $statement = $connection->query($select);

        $row = $statement->fetch();

        return [$row['data']];
    }
}
