<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\ResourceModel\File\Storage\Directory;

/**
 * Class Database
 */
class Database extends \Magento\MediaStorage\Model\ResourceModel\File\Storage\AbstractStorage
{
    /**
     * Define table name and id field for resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('media_storage_directory_storage', 'directory_id');
    }

    /**
     * Create database scheme for storing files
     *
     * @return $this
     */
    public function createDatabaseScheme()
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        if ($connection->isTableExists($table)) {
            return $this;
        }

        $ddlTable = $connection->newTable(
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
            $connection->getIndexName(
                $table,
                ['name', 'parent_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['name', 'parent_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $connection->getIndexName($table, ['parent_id']),
            ['parent_id']
        )->addForeignKey(
            $connection->getForeignKeyName($table, 'parent_id', $table, 'directory_id'),
            'parent_id',
            $table,
            'directory_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Directory Storage'
        );

        $connection->createTable($ddlTable);
        return $this;
    }

    /**
     * Load entity by path
     *
     * @param  \Magento\MediaStorage\Model\File\Storage\Directory\Database $object
     * @param  string $path
     * @return $this
     */
    public function loadByPath(\Magento\MediaStorage\Model\File\Storage\Directory\Database $object, $path)
    {
        $connection = $this->getConnection();

        $name = basename($path);
        $path = dirname($path);
        if ($path == '.') {
            $path = '';
        }

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'name = ?',
            $name
        )->where(
            $connection->prepareSqlCondition('path', ['seq' => $path])
        );

        $data = $connection->fetchRow($select);
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
        $connection = $this->getConnection();

        $name = basename($path);
        $path = dirname($path);
        if ($path == '.') {
            $path = '';
        }

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            ['directory_id']
        )->where(
            'name = ?',
            $name
        )->where(
            $connection->prepareSqlCondition('path', ['seq' => $path])
        );

        return $connection->fetchOne($select);
    }

    /**
     * Delete all directories from storage
     *
     * @return $this
     */
    public function clearDirectories()
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable());

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
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            ['name', 'path']
        )->order(
            'directory_id'
        )->limit(
            $count,
            $offset
        );

        return $connection->fetchAll($select);
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
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            ['name', 'path']
        )->where(
            $connection->prepareSqlCondition('path', ['seq' => $directory])
        )->order(
            'directory_id'
        );

        return $connection->fetchAll($select);
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
        $connection = $this->getConnection();

        $where = ['name = ?' => $name];
        $where[] = new \Zend_Db_Expr($connection->prepareSqlCondition('path', ['seq' => $path]));

        $connection->delete($this->getMainTable(), $where);
    }
}
