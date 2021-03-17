<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\ResourceModel\File\Storage;

/**
 * Class Database
 *
 * @api
 * @since 100.0.2
 *
 * @deprecated Database Media Storage is deprecated
 */
class Database extends \Magento\MediaStorage\Model\ResourceModel\File\Storage\AbstractStorage
{
    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_resourceHelper = $resourceHelper;
    }

    /**
     * Define table name and id field for resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('media_storage_file_storage', 'file_id');
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

        $dirStorageTable = $this->getTable('media_storage_directory_storage');
        // For foreign key

        $ddlTable = $connection->newTable(
            $table
        )->addColumn(
            'file_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'File Id'
        )->addColumn(
            'content',
            \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY,
            \Magento\Framework\DB\Ddl\Table::MAX_VARBINARY_SIZE,
            ['nullable' => false],
            'File Content'
        )->addColumn(
            'upload_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Upload Timestamp'
        )->addColumn(
            'filename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false],
            'Filename'
        )->addColumn(
            'directory_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'default' => null],
            'Identifier of Directory where File is Located'
        )->addColumn(
            'directory',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['default' => null],
            'Directory Path'
        )->addIndex(
            $connection->getIndexName(
                $table,
                ['filename', 'directory_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['filename', 'directory_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $connection->getIndexName($table, ['directory_id']),
            ['directory_id']
        )->addForeignKey(
            $connection->getForeignKeyName($table, 'directory_id', $dirStorageTable, 'directory_id'),
            'directory_id',
            $dirStorageTable,
            'directory_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'File Storage'
        );

        $connection->createTable($ddlTable);
        return $this;
    }

    /**
     * Decodes blob content retrieved by DB driver
     *
     * @param  array $row Table row with 'content' key in it
     * @return array
     */
    protected function _decodeFileContent($row)
    {
        $row['content'] = $this->getConnection()->decodeVarbinary($row['content']);
        return $row;
    }

    /**
     * Decodes blob content retrieved by Database driver
     *
     * @param  array $rows Array of table rows (files), each containing 'content' key
     * @return array
     */
    protected function _decodeAllFilesContent($rows)
    {
        foreach ($rows as $key => $row) {
            $rows[$key] = $this->_decodeFileContent($row);
        }
        return $rows;
    }

    /**
     * Load entity by filename
     *
     * @param  \Magento\MediaStorage\Model\File\Storage\Database $object
     * @param  string $filename
     * @param  string $path
     * @return $this
     */
    public function loadByFilename(\Magento\MediaStorage\Model\File\Storage\Database $object, $filename, $path)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'filename = ?',
            $filename
        )->where(
            $connection->prepareSqlCondition('directory', ['seq' => $path])
        );

        $row = $connection->fetchRow($select);
        if ($row) {
            $row = $this->_decodeFileContent($row);
            $object->setData($row);
            $this->_afterLoad($object);
        }

        return $this;
    }

    /**
     * Clear files in storage
     *
     * @return $this
     */
    public function clearFiles()
    {
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable());

        return $this;
    }

    /**
     * Get files from storage at defined range
     *
     * @param  int $offset
     * @param  int $count
     * @return array
     */
    public function getFiles($offset = 0, $count = 100)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            ['filename', 'content', 'directory']
        )->order(
            'file_id'
        )->limit(
            $count,
            $offset
        );

        $rows = $connection->fetchAll($select);
        return $this->_decodeAllFilesContent($rows);
    }

    /**
     * Save file to storage
     *
     * @param  array $file
     * @return $this
     */
    public function saveFile($file)
    {
        $connection = $this->getConnection();

        $contentParam = new \Magento\Framework\DB\Statement\Parameter($file['content']);
        $contentParam->setIsBlob(true);
        $data = [
            'content' => $contentParam,
            'upload_time' => $file['update_time'],
            'filename' => $file['filename'],
            'directory_id' => $file['directory_id'],
            'directory' => $file['directory'],
        ];

        $connection->insertOnDuplicate($this->getMainTable(), $data, ['content', 'upload_time']);

        return $this;
    }

    /**
     * Rename files in database
     *
     * @param  string $oldFilename
     * @param  string $oldPath
     * @param  string $newFilename
     * @param  string $newPath
     * @return $this
     */
    public function renameFile($oldFilename, $oldPath, $newFilename, $newPath)
    {
        $connection = $this->getConnection();
        $dataUpdate = ['filename' => $newFilename, 'directory' => $newPath];

        $dataWhere = ['filename = ?' => $oldFilename];
        $dataWhere[] = new \Zend_Db_Expr($connection->prepareSqlCondition('directory', ['seq' => $oldPath]));

        $connection->update($this->getMainTable(), $dataUpdate, $dataWhere);

        return $this;
    }

    /**
     * Copy files in database
     *
     * @param  string $oldFilename
     * @param  string $oldPath
     * @param  string $newFilename
     * @param  string $newPath
     * @return $this
     */
    public function copyFile($oldFilename, $oldPath, $newFilename, $newPath)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'filename = ?',
            $oldFilename
        )->where(
            $connection->prepareSqlCondition('directory', ['seq' => $oldPath])
        );

        $data = $connection->fetchRow($select);
        if (!$data) {
            return $this;
        }

        if (isset($data['file_id']) && isset($data['filename'])) {
            unset($data['file_id']);
            $data['filename'] = $newFilename;
            $data['directory'] = $newPath;

            $connection = $this->getConnection();
            $connection->insertOnDuplicate($this->getMainTable(), $data, ['content', 'upload_time']);
        }

        return $this;
    }

    /**
     * Check whether file exists in DB
     *
     * @param string $filename
     * @param string $path
     * @return bool
     */
    public function fileExists($filename, $path)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'filename = ?',
            $filename
        )->where(
            $connection->prepareSqlCondition('directory', ['seq' => $path])
        )->limit(
            1
        );

        $data = $connection->fetchRow($select);
        return (bool)$data;
    }

    /**
     * Delete files that starts with given $folderName
     *
     * @param string $folderName
     * @return void
     */
    public function deleteFolder($folderName = '')
    {
        $folderName = rtrim($folderName, '/');
        if (!strlen($folderName)) {
            return;
        }

        $this->getConnection()->delete(
            $this->getMainTable(),
            new \Zend_Db_Expr(
                'directory LIKE ' .
                $this->_resourceHelper->addLikeEscape($folderName . '/', ['position' => 'start'])
                . ' ' . \Magento\Framework\DB\Select::SQL_OR . ' ' .
                $this->getConnection()->prepareSqlCondition('directory', ['seq' => $folderName])
            )
        );
    }

    /**
     * Delete file
     *
     * @param string $filename
     * @param string $directory
     * @return void
     */
    public function deleteFile($filename, $directory)
    {
        $connection = $this->getConnection();

        $where = ['filename = ?' => $filename];
        $where[] = new \Zend_Db_Expr($connection->prepareSqlCondition('directory', ['seq' => $directory]));

        $connection->delete($this->getMainTable(), $where);
    }

    /**
     * Return directory file listing
     *
     * @param string $directory
     * @return array
     */
    public function getDirectoryFiles($directory)
    {
        $directory = trim($directory, '/');
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->getMainTable()],
            ['filename', 'directory', 'content']
        )->where(
            $connection->prepareSqlCondition('directory', ['seq' => $directory])
        )->order(
            'file_id'
        );

        $rows = $connection->fetchAll($select);
        return $this->_decodeAllFilesContent($rows);
    }
}
