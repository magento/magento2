<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

/**
 * Abstract resource model. Can be used as base for indexer resources
 *
 * @api
 */
abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     *
     * @var \Magento\Framework\Indexer\Table\StrategyInterface
     */
    protected $tableStrategy;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Indexer\Table\StrategyInterface $tableStrategy,
        $connectionName = null
    ) {
        $this->tableStrategy = $tableStrategy;
        parent::__construct($context, $connectionName);
    }

    /**
     * Reindex all
     *
     * @return $this
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);
        return $this;
    }

    /**
     * Get DB adapter for index data processing
     *
     * @return AdapterInterface
     */
    protected function _getIndexAdapter()
    {
        return $this->getConnection();
    }

    /**
     * Get index table name with additional suffix
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        if ($table) {
            return $this->tableStrategy->prepareTableName($table);
        }
        return $this->tableStrategy->prepareTableName($this->getMainTable());
    }

    /**
     * Synchronize data between index storage and original storage
     *
     * @return $this
     */
    public function syncData()
    {
        $this->beginTransaction();
        try {
            /**
             * Can't use truncate because of transaction
             */
            $this->getConnection()->delete($this->getMainTable());
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable(), false);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Copy data from source table of read adapter to destination table of index adapter
     *
     * @param string $sourceTable
     * @param string $destTable
     * @param bool $readToIndex data migration direction (true - read=>index, false - index=>read)
     * @return $this
     */
    public function insertFromTable($sourceTable, $destTable, $readToIndex = true)
    {
        if ($readToIndex) {
            $sourceColumns = array_keys($this->getConnection()->describeTable($sourceTable));
            $targetColumns = array_keys($this->getConnection()->describeTable($destTable));
        } else {
            $sourceColumns = array_keys($this->_getIndexAdapter()->describeTable($sourceTable));
            $targetColumns = array_keys($this->getConnection()->describeTable($destTable));
        }
        $select = $this->_getIndexAdapter()->select()->from($sourceTable, $sourceColumns);

        $this->insertFromSelect($select, $destTable, $targetColumns, $readToIndex);
        return $this;
    }

    /**
     * Insert data from select statement of read adapter to
     * destination table related with index adapter
     *
     * @param Select $select
     * @param string $destTable
     * @param array $columns
     * @param bool $readToIndex data migration direction (true - read=>index, false - index=>read)
     * @return $this
     */
    public function insertFromSelect($select, $destTable, array $columns, $readToIndex = true)
    {
        if ($readToIndex) {
            $from = $this->getConnection();
            $to = $this->_getIndexAdapter();
        } else {
            $from = $this->_getIndexAdapter();
            $to = $this->getConnection();
        }

        if ($from === $to) {
            $query = $select->insertFromSelect($destTable, $columns);
            $to->query($query);
        } else {
            $stmt = $from->query($select);
            $data = [];
            $counter = 0;
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $data[] = $row;
                $counter++;
                if ($counter > 2000) {
                    $to->insertArray($destTable, $columns, $data);
                    $data = [];
                    $counter = 0;
                }
            }
            if (!empty($data)) {
                $to->insertArray($destTable, $columns, $data);
            }
        }

        return $this;
    }

    /**
     * Clean up temporary index table
     *
     * @return void
     */
    public function clearTemporaryIndexTable()
    {
        $this->getConnection()->delete($this->getIdxTable());
    }
}
