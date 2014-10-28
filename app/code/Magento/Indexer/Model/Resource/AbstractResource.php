<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract resource model. Can be used as base for indexer resources
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Indexer\Model\Resource;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

abstract class AbstractResource extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    const IDX_SUFFIX = '_idx';

    const TMP_SUFFIX = '_tmp';

    /**
     * Flag that defines if need to use "_idx" index table suffix instead of "_tmp"
     *
     * @var bool
     */
    protected $_isNeedUseIdxTable = false;

    /**
     * Reindex all
     *
     * @return $this
     */
    public function reindexAll()
    {
        $this->useIdxTable(true);
        return $this;
    }

    /**
     * Get DB adapter for index data processing
     *
     * @return AdapterInterface
     */
    protected function _getIndexAdapter()
    {
        return $this->_getWriteAdapter();
    }

    /**
     * Get index table name with additional suffix
     *
     * @param string $table
     * @return string
     */
    public function getIdxTable($table = null)
    {
        $suffix = self::TMP_SUFFIX;
        if ($this->_isNeedUseIdxTable) {
            $suffix = self::IDX_SUFFIX;
        }
        if ($table) {
            return $table . $suffix;
        }
        return $this->getMainTable() . $suffix;
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
            $this->_getWriteAdapter()->delete($this->getMainTable());
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
            $sourceColumns = array_keys($this->_getWriteAdapter()->describeTable($sourceTable));
            $targetColumns = array_keys($this->_getWriteAdapter()->describeTable($destTable));
        } else {
            $sourceColumns = array_keys($this->_getIndexAdapter()->describeTable($sourceTable));
            $targetColumns = array_keys($this->_getWriteAdapter()->describeTable($destTable));
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
            $from = $this->_getWriteAdapter();
            $to = $this->_getIndexAdapter();
        } else {
            $from = $this->_getIndexAdapter();
            $to = $this->_getWriteAdapter();
        }

        if ($from === $to) {
            $query = $select->insertFromSelect($destTable, $columns);
            $to->query($query);
        } else {
            $stmt = $from->query($select);
            $data = array();
            $counter = 0;
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $data[] = $row;
                $counter++;
                if ($counter > 2000) {
                    $to->insertArray($destTable, $columns, $data);
                    $data = array();
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
     * Set or get what either "_idx" or "_tmp" suffixed temporary index table need to use
     *
     * @param bool $value
     * @return bool
     */
    public function useIdxTable($value = null)
    {
        if (!is_null($value)) {
            $this->_isNeedUseIdxTable = (bool)$value;
        }
        return $this->_isNeedUseIdxTable;
    }

    /**
     * Clean up temporary index table
     *
     * @return void
     */
    public function clearTemporaryIndexTable()
    {
        $this->_getWriteAdapter()->delete($this->getIdxTable());
    }
}
