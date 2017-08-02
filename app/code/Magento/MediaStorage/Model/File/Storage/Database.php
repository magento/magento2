<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

/**
 * Class Database
 *
 * @api
 * @since 2.0.0
 */
class Database extends \Magento\MediaStorage\Model\File\Storage\Database\AbstractDatabase
{
    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'media_storage_file_storage_database';

    /**
     * Directory singleton
     *
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\Database
     * @since 2.0.0
     */
    protected $_directoryModel = null;

    /**
     * Collect errors during sync process
     *
     * @var string[]
     * @since 2.0.0
     */
    protected $_errors = [];

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory
     * @since 2.0.0
     */
    protected $_directoryFactory;

    /**
     * @var \Magento\MediaStorage\Helper\File\Media
     * @since 2.0.0
     */
    protected $_mediaHelper;

    /**
     * Store media base directory path
     *
     * @var string
     * @since 2.1.0
     */
    protected $mediaBaseDirectory = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateModel
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $configuration
     * @param \Magento\MediaStorage\Helper\File\Media $mediaHelper
     * @param \Magento\MediaStorage\Model\ResourceModel\File\Storage\Database $resource
     * @param Directory\DatabaseFactory $directoryFactory
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $connectionName
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $configuration,
        \Magento\MediaStorage\Helper\File\Media $mediaHelper,
        \Magento\MediaStorage\Model\ResourceModel\File\Storage\Database $resource,
        \Magento\MediaStorage\Model\File\Storage\Directory\DatabaseFactory $directoryFactory,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $connectionName = null,
        array $data = []
    ) {
        $this->_directoryFactory = $directoryFactory;
        $this->_mediaHelper = $mediaHelper;
        parent::__construct(
            $context,
            $registry,
            $coreFileStorageDb,
            $dateModel,
            $configuration,
            $resource,
            $resourceCollection,
            $connectionName,
            $data
        );
        $this->_init(get_class($this->_resource));
    }

    /**
     * Retrieve directory model
     *
     * @return \Magento\MediaStorage\Model\File\Storage\Directory\Database
     * @since 2.0.0
     */
    public function getDirectoryModel()
    {
        if ($this->_directoryModel === null) {
            $this->_directoryModel = $this->_directoryFactory->create(
                ['connectionName' => $this->getConnectionName()]
            );
        }

        return $this->_directoryModel;
    }

    /**
     * Create tables for file and directory storages
     *
     * @return $this
     * @since 2.0.0
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
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getStorageName()
    {
        return __('database "%1"', $this->getConnectionName());
    }

    /**
     * Load object data by filename
     *
     * @param  string $filePath
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function hasErrors()
    {
        return !empty($this->_errors) || $this->getDirectoryModel()->hasErrors();
    }

    /**
     * Clear files and directories in storage
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function exportDirectories($offset = 0, $count = 100)
    {
        return $this->getDirectoryModel()->exportDirectories($offset, $count);
    }

    /**
     * Import directories to storage
     *
     * @param  array $dirs
     * @return \Magento\MediaStorage\Model\File\Storage\Directory\Database
     * @since 2.0.0
     */
    public function importDirectories($dirs)
    {
        return $this->getDirectoryModel()->importDirectories($dirs);
    }

    /**
     * Export files list in defined range
     *
     * @param  int $offset
     * @param  int $count
     * @return array|bool
     * @since 2.0.0
     */
    public function exportFiles($offset = 0, $count = 100)
    {
        $offset = (int)$offset >= 0 ? (int)$offset : 0;
        $count = (int)$count >= 1 ? (int)$count : 1;

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
     * @return $this
     * @since 2.0.0
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
                $file['directory_id'] = isset(
                    $file['directory']
                ) && strlen(
                    $file['directory']
                ) ? $this->_directoryFactory->create(
                    ['connectionName' => $this->getConnectionName()]
                )->loadByPath(
                    $file['directory']
                )->getId() : null;

                $this->_getResource()->saveFile($file);
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                $this->_logger->critical($e);
            }
        }

        return $this;
    }

    /**
     * Store file into database
     *
     * @param  string $filename
     * @return $this
     * @since 2.0.0
     */
    public function saveFile($filename)
    {
        $fileInfo = $this->_mediaHelper->collectFileInfo($this->getMediaBaseDirectory(), $filename);
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
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
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
     * @return array
     * @since 2.0.0
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
     * @return $this
     * @since 2.0.0
     */
    public function deleteFile($path)
    {
        $filename = basename($path);
        $directory = dirname($path);
        $this->_getResource()->deleteFile($filename, $directory);

        return $this;
    }

    /**
     * Retrieve media base directory path
     *
     * @return string
     * @since 2.1.0
     */
    public function getMediaBaseDirectory()
    {
        if ($this->mediaBaseDirectory === null) {
            $this->mediaBaseDirectory = $this->_coreFileStorageDb->getMediaBaseDir();
        }
        return $this->mediaBaseDirectory;
    }
}
