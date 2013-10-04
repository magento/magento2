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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * File storage database model class
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\File\Storage;

class Database extends \Magento\Core\Model\File\Storage\Database\AbstractDatabase
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_file_storage_database';

    /**
     * Directory singleton
     *
     * @var \Magento\Core\Model\File\Storage\Directory\Database
     */
    protected $_directoryModel = null;

    /**
     * Collect errors during sync process
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @var \Magento\Core\Model\File\Storage\Directory\DatabaseFactory
     */
    protected $_directoryFactory;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Date $dateModel
     * @param \Magento\Core\Model\App $app
     * @param \Magento\Core\Model\Resource\File\Storage\Database $resource
     * @param \Magento\Core\Model\File\Storage\Directory\DatabaseFactory $directoryFactory
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Date $dateModel,
        \Magento\Core\Model\App $app,
        \Magento\Core\Model\Resource\File\Storage\Database $resource,
        \Magento\Core\Model\File\Storage\Directory\DatabaseFactory $directoryFactory,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array(),
        $connectionName = null
    ) {
        $this->_init('Magento\Core\Model\Resource\File\Storage\Database');
        $this->_directoryFactory = $directoryFactory;
        $this->_logger = $logger;
        parent::__construct(
            $coreFileStorageDb,
            $context,
            $registry,
            $dateModel,
            $app,
            $resource,
            $resourceCollection,
            $data);
    }

    /**
     * Retrieve directory model
     *
     * @return \Magento\Core\Model\File\Storage\Directory\Database
     */
    public function getDirectoryModel()
    {
        if (is_null($this->_directoryModel)) {
            $arguments = array('connection' => $this->getConnectionName());
            $this->_directoryModel = $this->_directoryFactory->create(array('connectionName' => $arguments));
        }

        return $this->_directoryModel;
    }

    /**
     * Create tables for file and directory storages
     *
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function init()
    {
        $this->getDirectoryModel()->prepareStorage();
        $this->prepareStorage();

        return $this;
    }

    /**
     * Return storage name
     *
     * @return string
     */
    public function getStorageName()
    {
        return __('database "%1"', $this->getConnectionName());
    }

    /**
     * Load object data by filename
     *
     * @param  string $filePath
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function loadByFilename($filePath)
    {
        $filename = basename($filePath);
        $path = dirname($filePath);
        $this->_getResource()->loadByFilename($this, $filename, $path);
        return $this;
    }

    /**
     * Check if there was errors during sync process
     *
     * @return bool
     */
    public function hasErrors()
    {
        return (!empty($this->_errors) || $this->getDirectoryModel()->hasErrors());
    }

    /**
     * Clear files and directories in storage
     *
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function clear()
    {
        $this->getDirectoryModel()->clearDirectories();
        $this->_getResource()->clearFiles();
        return $this;
    }

    /**
     * Export directories from storage
     *
     * @param  int $offset
     * @param  int $count
     * @return bool|array
     */
    public function exportDirectories($offset = 0, $count = 100) {
        return $this->getDirectoryModel()->exportDirectories($offset, $count);
    }

    /**
     * Import directories to storage
     *
     * @param  array $dirs
     * @return \Magento\Core\Model\File\Storage\Directory\Database
     */
    public function importDirectories($dirs) {
        return $this->getDirectoryModel()->importDirectories($dirs);
    }

    /**
     * Export files list in defined range
     *
     * @param  int $offset
     * @param  int $count
     * @return array|bool
     */
    public function exportFiles($offset = 0, $count = 100)
    {
        $offset = ((int) $offset >= 0) ? (int) $offset : 0;
        $count  = ((int) $count >= 1) ? (int) $count : 1;

        $result = $this->_getResource()->getFiles($offset, $count);
        if (empty($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Import files list
     *
     * @param  array $files
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function importFiles($files)
    {
        if (!is_array($files)) {
            return $this;
        }

        $dateSingleton = $this->_date;
        foreach ($files as $file) {
            if (!isset($file['filename']) || !strlen($file['filename']) || !isset($file['content'])) {
                continue;
            }

            try {
                $file['update_time'] = $dateSingleton->date();
                $arguments = array('connection' => $this->getConnectionName());
                $file['directory_id'] = (isset($file['directory']) && strlen($file['directory']))
                    ? $this->_directoryFactory->create(array('connectionName' => $arguments))
                        ->loadByPath($file['directory'])->getId()
                    : null;

                $this->_getResource()->saveFile($file);
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                $this->_logger->logException($e);
            }
        }

        return $this;
    }

    /**
     * Store file into database
     *
     * @param  string $filename
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function saveFile($filename)
    {
        $fileInfo = $this->collectFileInfo($filename);
        $filePath = $fileInfo['directory'];

        $directory = $this->_directoryFactory->create()->loadByPath($filePath);

        if (!$directory->getId()) {
            $directory = $this->getDirectoryModel()->createRecursive($filePath);
        }

        $fileInfo['directory_id'] = $directory->getId();
        $this->_getResource()->saveFile($fileInfo);

        return $this;
    }

    /**
     * Check whether file exists in DB
     *
     * @param  string $filePath
     * @return bool
     */
    public function fileExists($filePath)
    {
        return $this->_getResource()->fileExists(basename($filePath), dirname($filePath));
    }

    /**
     * Copy files
     *
     * @param  string $oldFilePath
     * @param  string $newFilePath
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function copyFile($oldFilePath, $newFilePath)
    {
        $this->_getResource()->copyFile(
            basename($oldFilePath),
            dirname($oldFilePath),
            basename($newFilePath),
            dirname($newFilePath)
        );

        return $this;
    }

    /**
     * Rename files in database
     *
     * @param  string $oldFilePath
     * @param  string $newFilePath
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function renameFile($oldFilePath, $newFilePath)
    {
        $this->_getResource()->renameFile(
            basename($oldFilePath),
            dirname($oldFilePath),
            basename($newFilePath),
            dirname($newFilePath)
        );

        $newPath = dirname($newFilePath);
        $directory = $this->_directoryFactory->create()->loadByPath($newPath);

        if (!$directory->getId()) {
            $directory = $this->getDirectoryModel()->createRecursive($newPath);
        }

        $this->loadByFilename($newFilePath);
        if ($this->getId()) {
            $this->setDirectoryId($directory->getId())->save();
        }

        return $this;
    }

    /**
     * Return directory listing
     *
     * @param string $directory
     * @return mixed
     */
    public function getDirectoryFiles($directory)
    {
        $directory = $this->_coreFileStorageDb->getMediaRelativePath($directory);
        return $this->_getResource()->getDirectoryFiles($directory);
    }

    /**
     * Delete file from database
     *
     * @param string $path
     * @return \Magento\Core\Model\File\Storage\Database
     */
    public function deleteFile($path)
    {
        $filename = basename($path);
        $directory = dirname($path);
        $this->_getResource()->deleteFile($filename, $directory);

        return $this;
    }
}
