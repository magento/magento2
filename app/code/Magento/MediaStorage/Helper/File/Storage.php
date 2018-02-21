<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * File storage helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\MediaStorage\Helper\File;

class Storage extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Current storage code
     *
     * @var int
     */
    protected $_currentStorage = null;

    /**
     * List of internal storages
     *
     * @var int[]
     */
    protected $_internalStorageList = [\Magento\MediaStorage\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM];

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage
     */
    protected $_storage;

    /**
     * File system storage model
     *
     * @var \Magento\MediaStorage\Model\File\Storage\File
     */
    protected $_filesystemStorage;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\MediaStorage\Model\File\Storage $storage
     * @param \Magento\MediaStorage\Model\File\Storage\File $filesystemStorage
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Model\File\Storage $storage,
        \Magento\MediaStorage\Model\File\Storage\File $filesystemStorage
    ) {
        $this->_filesystemStorage = $filesystemStorage;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_storage = $storage;
        parent::__construct($context);
    }

    /**
     * Return saved storage code
     *
     * @return int
     */
    public function getCurrentStorageCode()
    {
        if ($this->_currentStorage === null) {
            $this->_currentStorage = (int)$this->scopeConfig->getValue(
                \Magento\MediaStorage\Model\File\Storage::XML_PATH_STORAGE_MEDIA,
                'default'
            );
        }

        return $this->_currentStorage;
    }

    /**
     * Retrieve file system storage model
     *
     * @return \Magento\MediaStorage\Model\File\Storage\File
     */
    public function getStorageFileModel()
    {
        return $this->_filesystemStorage;
    }

    /**
     * Check if storage is internal
     *
     * @param  int|null $storage
     * @return bool
     */
    public function isInternalStorage($storage = null)
    {
        $storage = $storage !== null ? (int)$storage : $this->getCurrentStorageCode();

        return in_array($storage, $this->_internalStorageList);
    }

    /**
     * Retrieve storage model
     *
     * @param  int|null $storage
     * @param  array $params
     * @return \Magento\Framework\Model\AbstractModel|bool
     */
    public function getStorageModel($storage = null, $params = [])
    {
        return $this->_storage->getStorageModel($storage, $params);
    }

    /**
     * Check if needed to copy file from storage to file system and
     * if file exists in the storage
     *
     * @param  string $filename
     * @return bool|int
     */
    public function processStorageFile($filename)
    {
        if ($this->isInternalStorage()) {
            return false;
        }

        $dbHelper = $this->_coreFileStorageDb;

        $relativePath = $dbHelper->getMediaRelativePath($filename);
        $file = $this->getStorageModel()->loadByFilename($relativePath);

        if (!$file->getId()) {
            return false;
        }

        return $this->saveFileToFileSystem($file);
    }

    /**
     * Save file to file system
     *
     * @param  \Magento\MediaStorage\Model\File\Storage\Database $file
     * @return bool|int
     */
    public function saveFileToFileSystem($file)
    {
        return $this->getStorageFileModel()->saveFile($file, true);
    }
}
