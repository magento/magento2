<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

/**
 * Class File
 *
 * @api
 * @since 100.0.2
 */
class File
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'media_storage_file_storage_file';

    /**
     * Store media base directory path
     *
     * @var string
     */
    protected $_mediaBaseDirectory = null;

    /**
     * Core file storage database
     *
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_storageHelper = null;

    /**
     * @var \Magento\MediaStorage\Helper\File\Media
     */
    protected $_mediaHelper = null;

    /**
     * Data at storage
     *
     * @var array
     */
    protected $_data = null;

    /**
     * Collect errors during sync process
     *
     * @var string[]
     */
    protected $_errors = [];

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $storageHelper
     * @param \Magento\MediaStorage\Helper\File\Media $mediaHelper
     * @param \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\MediaStorage\Helper\File\Storage\Database $storageHelper,
        \Magento\MediaStorage\Helper\File\Media $mediaHelper,
        \Magento\MediaStorage\Model\ResourceModel\File\Storage\File $fileUtility
    ) {
        $this->_fileUtility = $fileUtility;
        $this->_storageHelper = $storageHelper;
        $this->_logger = $logger;
        $this->_mediaHelper = $mediaHelper;
    }

    /**
     * Initialization
     *
     * @return $this
     */
    public function init()
    {
        return $this;
    }

    /**
     * Return storage name
     *
     * @return \Magento\Framework\Phrase
     */
    public function getStorageName()
    {
        return __('File system');
    }

    /**
     * Get files and directories from storage
     *
     * @return array
     */
    public function getStorageData()
    {
        return $this->_fileUtility->getStorageData();
    }

    /**
     * Check if there was errors during sync process
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    /**
     * Clear files and directories in storage
     *
     * @return $this
     */
    public function clear()
    {
        $this->_fileUtility->clear();
        return $this;
    }

    /**
     * Collect files and directories from storage
     *
     * @param  int $offset
     * @param  int $count
     * @param  string $type
     * @return array|bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectData($offset = 0, $count = 100, $type = 'files')
    {
        if (!in_array($type, ['files', 'directories'])) {
            return false;
        }

        $offset = (int)$offset >= 0 ? (int)$offset : 0;
        $count = (int)$count >= 1 ? (int)$count : 1;

        if (empty($this->_data)) {
            $this->_data = $this->getStorageData();
        }

        if (!array_key_exists($type, $this->_data)) {
            return false;
        }
        $slice = array_slice($this->_data[$type], $offset, $count);
        return $slice ?: false;
    }

    /**
     * Retrieve connection name saved at config
     *
     * @return null
     */
    public function getConfigConnectionName()
    {
        return null;
    }

    /**
     * Retrieve connection name
     *
     * @return null
     */
    public function getConnectionName()
    {
        return null;
    }

    /**
     * Export directories list from storage
     *
     * @param  int $offset
     * @param  int $count
     * @return array|bool
     */
    public function exportDirectories($offset = 0, $count = 100)
    {
        return $this->collectData($offset, $count, 'directories');
    }

    /**
     * Export files list in defined range
     *
     * @param  int $offset
     * @param  int $count
     * @return array|bool
     */
    public function exportFiles($offset = 0, $count = 1)
    {
        $slice = $this->collectData($offset, $count, 'files');

        if (!$slice) {
            return false;
        }

        $result = [];
        foreach ($slice as $fileName) {
            try {
                $fileInfo = $this->_mediaHelper->collectFileInfo($this->getMediaBaseDirectory(), $fileName);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                continue;
            }

            $result[] = $fileInfo;
        }

        return $result;
    }

    /**
     * Import entities to storage
     *
     * @param  array $data
     * @param  string $callback
     * @return $this
     */
    public function import($data, $callback)
    {
        if (!is_array($data) || !method_exists($this, $callback)) {
            return $this;
        }

        foreach ($data as $part) {
            try {
                $this->{$callback}($part);
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                $this->_logger->critical($e);
            }
        }

        return $this;
    }

    /**
     * Import directories to storage
     *
     * @param  array $dirs
     * @return $this
     */
    public function importDirectories($dirs)
    {
        return $this->import($dirs, 'saveDir');
    }

    /**
     * Import files list
     *
     * @param  array $files
     * @return $this
     */
    public function importFiles($files)
    {
        return $this->import($files, 'saveFile');
    }

    /**
     * Save directory to storage
     *
     * @param  array $dir
     * @return bool
     */
    public function saveDir($dir)
    {
        return $this->_fileUtility->saveDir($dir);
    }

    /**
     * Save file to storage
     *
     * @param  array $file
     * @param  bool $overwrite
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function saveFile($file, $overwrite = true)
    {
        if (isset(
            $file['filename']
        ) && !empty($file['filename']) && isset(
            $file['content']
        ) && !empty($file['content'])
        ) {
            try {
                $filename = isset(
                    $file['directory']
                ) && !empty($file['directory']) ? $file['directory'] . '/' . $file['filename'] : $file['filename'];

                return $this->_fileUtility->saveFile($filename, $file['content'], $overwrite);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Unable to save file "%1" at "%2"', $file['filename'], $file['directory'])
                );
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong file info format'));
        }

        return false;
    }

    /**
     * Retrieve media base directory path
     *
     * @return string
     */
    public function getMediaBaseDirectory()
    {
        if ($this->_mediaBaseDirectory === null) {
            $this->_mediaBaseDirectory = $this->_storageHelper->getMediaBaseDir();
        }
        return $this->_mediaBaseDirectory;
    }
}
