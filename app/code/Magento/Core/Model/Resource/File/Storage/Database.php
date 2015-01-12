<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\File\Storage;

/**
 * Class Database
 */
class Database extends \Magento\Core\Model\Resource\File\Storage\AbstractStorage
{
    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\DB\Helper $resourceHelper
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\DB\Helper $resourceHelper)
    {
        parent::__construct($resource);
        $this->_resourceHelper = $resourceHelper;
    }

    /**
     * Define table name and id field for resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_file_storage', 'file_id');
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

        $dirStorageTable = $this->getTable('core_directory_storage');
        // For foreign key

        $ddlTable = $adapter->newTable(
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
            $adapter->getIndexName(
                $table,
                ['filename', 'directory_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['filename', 'directory_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $adapter->getIndexName($table, ['directory_id']),
            ['directory_id']
        )->addForeignKey(
            $adapter->getForeignKeyName($table, 'directory_id', $dirStorageTable, 'directory_id'),
            'directory_id',
            $dirStorageTable,
            'directory_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'File Storage'
        );

        $adapter->createTable($ddlTable);
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
        $row['content'] = $this->_getReadAdapter()->decodeVarbinary($row['content']);
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
     * @param  \Magento\Core\Model\File\Storage\Database $object
     * @param  string $filename
     * @param  string $path
     * @return $this
     */
    public function loadByFilename(\Magento\Core\Model\File\Storage\Database $object, $filename, $path)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'filename = ?',
            $filename
        )->where(
            $adapter->prepareSqlCondition('directory', ['seq' => $path])
        );

        $row = $adapter->fetchRow($select);
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
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getMainTable());

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
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()],
            ['filename', 'content', 'directory']
        )->order(
            'file_id'
        )->limit(
            $count,
            $offset
        );

        $rows = $adapter->fetchAll($select);
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
        $adapter = $this->_getWriteAdapter();

        $contentParam = new \Magento\Framework\DB\Statement\Parameter($file['content']);
        $contentParam->setIsBlob(true);
        $data = [
            'content' => $contentParam,
            'upload_time' => $file['update_time'],
            'filename' => $file['filename'],
            'directory_id' => $file['directory_id'],
            'directory' => $file['directory'],
        ];

        $adapter->insertOnDuplicate($this->getMainTable(), $data, ['content', 'upload_time']);

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
        $adapter = $this->_getWriteAdapter();
        $dataUpdate = ['filename' => $newFilename, 'directory' => $newPath];

        $dataWhere = ['filename = ?' => $oldFilename];
        $dataWhere[] = new \Zend_Db_Expr($adapter->prepareSqlCondition('directory', ['seq' => $oldPath]));

        $adapter->update($this->getMainTable(), $dataUpdate, $dataWhere);

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
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'filename = ?',
            $oldFilename
        )->where(
            $adapter->prepareSqlCondition('directory', ['seq' => $oldPath])
        );

        $data = $adapter->fetchRow($select);
        if (!$data) {
            return $this;
        }

        if (isset($data['file_id']) && isset($data['filename'])) {
            unset($data['file_id']);
            $data['filename'] = $newFilename;
            $data['directory'] = $newPath;

            $writeAdapter = $this->_getWriteAdapter();
            $writeAdapter->insertOnDuplicate($this->getMainTable(), $data, ['content', 'upload_time']);
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
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()]
        )->where(
            'filename = ?',
            $filename
        )->where(
            $adapter->prepareSqlCondition('directory', ['seq' => $path])
        )->limit(
            1
        );

        $data = $adapter->fetchRow($select);
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

        $likeExpression = $this->_resourceHelper->addLikeEscape($folderName . '/', ['position' => 'start']);
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            new \Zend_Db_Expr('filename LIKE ' . $likeExpression)
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
        $adapter = $this->_getWriteAdapter();

        $where = ['filename = ?' => $filename];
        $where[] = new \Zend_Db_Expr($adapter->prepareSqlCondition('directory', ['seq' => $directory]));

        $adapter->delete($this->getMainTable(), $where);
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
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()->from(
            ['e' => $this->getMainTable()],
            ['filename', 'directory', 'content']
        )->where(
            $adapter->prepareSqlCondition('directory', ['seq' => $directory])
        )->order(
            'file_id'
        );

        $rows = $adapter->fetchAll($select);
        return $this->_decodeAllFilesContent($rows);
    }
}
