<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\File\Storage\Directory;

/**
 * Class Database
 */
class Database extends \Magento\Core\Model\Resource\File\Storage\AbstractStorage
{
    /**
     * Define table name and id field for resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_directory_storage', 'directory_id');
    }

    /**
     * Create database scheme for storing files
     *
     * @return $this
     */
    public function createDatabaseScheme()
    {
        $adapter = $this->_getWriteAdapter();
        $table = $this->getMainTable();
        if ($adapter->isTableExists($table)) {
            return $this;
        }

        $ddlTable = $adapter->newTable(
            $table
        )->addColumn(
            'directory_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Directory Id'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Directory Name'
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => null],
            'Path to the \Directory'
        )->addColumn(
            'upload_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Upload Timestamp'
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true, 'default' => null, 'unsigned' => true],
            'Parent \Directory Id'
        )->addIndex(
            $adapter->getIndexName(
                $table,
                ['name', 'parent_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name', 'parent_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $adapter->getIndexName($table, ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $adapter->getForeignKeyName($table, 'parent_id', $table, 'directory_id'),
            'parent_id',
            $table,
            'directory_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Directory Storage'
        );

        $adapter->createTable($ddlTable);
        return $this;
    }

    /**
     * Load entity by path
     *
     * @param  \Magento\Core\Model\File\Storage\Directory\Database $object
     * @param  string $path
     * @return $this
     */
    public function loadByPath(\Magento\Core\Model\File\Storage\Directory\Database $object, $path)
    {
        $adapter = $this->_getReadAdapter();

        $name = basename($path);
        $path = dirname($path);
        if ($path == '.') {
            $path = '';
        }

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'name = ?',
            $name
        )->where(
            $adapter->prepareSqlCondition('path', ['seq' => $path])
        );

        $data = $adapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
        }

        return $this;
    }

    /**
     * Return parent id
     *
     * @param string $path
     * @return int
     */
    public function getParentId($path)
    {
        $adapter = $this->_getReadAdapter();

        $name = basename($path);
        $path = dirname($path);
        if ($path == '.') {
            $path = '';
        }

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()],
            ['directory_id']
        )->where(
            'name = ?',
            $name
        )->where(
            $adapter->prepareSqlCondition('path', ['seq' => $path])
        );

        return $adapter->fetchOne($select);
    }

    /**
     * Delete all directories from storage
     *
     * @return $this
     */
    public function clearDirectories()
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getMainTable());

        return $this;
    }

    /**
     * Export directories from database
     *
     * @param int $offset
     * @param int $count
     * @return array
     */
    public function exportDirectories($offset, $count = 100)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()],
            ['name', 'path']
        )->order(
            'directory_id'
        )->limit(
            $count,
            $offset
        );

        return $adapter->fetchAll($select);
    }

    /**
     * Return directory file listing
     *
     * @param string $directory
     * @return array
     */
    public function getSubdirectories($directory)
    {
        $directory = trim($directory, '/');
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()],
            ['name', 'path']
        )->where(
            $adapter->prepareSqlCondition('path', ['seq' => $directory])
        )->order(
            'directory_id'
        );

        return $adapter->fetchAll($select);
    }

    /**
     * Delete directory
     *
     * @param string $name
     * @param string $path
     * @return void
     */
    public function deleteDirectory($name, $path)
    {
        $adapter = $this->_getWriteAdapter();

        $where = ['name = ?' => $name];
        $where[] = new \Zend_Db_Expr($adapter->prepareSqlCondition('path', ['seq' => $path]));

        $adapter->delete($this->getMainTable(), $where);
    }
}
