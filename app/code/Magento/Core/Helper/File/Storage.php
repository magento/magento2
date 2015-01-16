<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * File storage helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Helper\File;

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
    protected $_internalStorageList = [\Magento\Core\Model\File\Storage::STORAGE_MEDIA_FILE_SYSTEM];

    /**
     * Core file storage database
     *
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_coreFileStorageDb = null;

    /**
     * @var \Magento\Core\Model\File\Storage
     */
    protected $_storage;

    /**
     * File system storage model
     *
     * @var \Magento\Core\Model\File\Storage\File
     */
    protected $_filesystemStorage;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Core\Model\File\Storage $storage
     * @param \Magento\Core\Model\File\Storage\File $filesystemStorage
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Core\Model\File\Storage $storage,
        \Magento\Core\Model\File\Storage\File $filesystemStorage,
        \Magento\Framework\App\Config\ScopeConfigInterface $config
    ) {
        $this->_filesystemStorage = $filesystemStorage;
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_storage = $storage;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Return saved storage code
     *
     * @return int
     */
    public function getCurrentStorageCode()
    {
        if (is_null($this->_currentStorage)) {
            $this->_currentStorage = (int)$this->config->getValue(
                \Magento\Core\Model\File\Storage::XML_PATH_STORAGE_MEDIA,
                'default'
            );
        }

        return $this->_currentStorage;
    }

    /**
     * Retrieve file system storage model
     *
     * @return \Magento\Core\Model\File\Storage\File
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
        $storage = !is_null($storage) ? (int)$storage : $this->getCurrentStorageCode();

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
     * @param  \Magento\Core\Model\File\Storage\Database $file
     * @return bool|int
     */
    public function saveFileToFileSystem($file)
    {
        return $this->getStorageFileModel()->saveFile($file, true);
    }
}
