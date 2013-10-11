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
 * Abstract model class
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\File\Storage;

class File extends \Magento\Core\Model\File\Storage\AbstractStorage
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_file_storage_file';

    /**
     * Data at storage
     *
     * @var array
     */
    protected $_data = null;

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
     * Class construct
     *
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\File\Storage\File $resource
     * @param \Magento\Data\Collection\Db|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\Core\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\File\Storage\File $resource,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($coreFileStorageDb, $context, $registry, $resource, $resourceCollection, $data);
        $this->_setResourceModel('Magento\Core\Model\Resource\File\Storage\File');
        $this->_logger = $logger;
    }

    /**
     * Initialization
     *
     * @return \Magento\Core\Model\File\Storage\File
     */
    public function init()
    {
        return $this;
    }

    /**
     * Return storage name
     *
     * @return string
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
        return $this->_getResource()->getStorageData();
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
     * @return \Magento\Core\Model\File\Storage\File
     */
    public function clear()
    {
        $this->_getResource()->clear();
        return $this;
    }

    /**
     * Collect files and directories from storage
     *
     * @param  int $offset
     * @param  int $count
     * @param  string $type
     * @return array|bool
     */
    public function collectData($offset = 0, $count = 100, $type = 'files')
    {
        if (!in_array($type, array('files', 'directories'))) {
            return false;
        }

        $offset = ((int) $offset >= 0) ? (int) $offset : 0;
        $count  = ((int) $count >= 1) ? (int) $count : 1;

        if (is_null($this->_data)) {
            $this->_data = $this->getStorageData();
        }

        $slice = array_slice($this->_data[$type], $offset, $count);
        if (empty($slice)) {
            return false;
        }

        return $slice;
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

        $result = array();
        foreach ($slice as $fileName) {
            try {
                $fileInfo = $this->collectFileInfo($fileName);
            } catch (\Exception $e) {
                $this->_logger->logException($e);
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
     * @return \Magento\Core\Model\File\Storage\File
     */
    public function import($data, $callback)
    {
        if (!is_array($data) || !method_exists($this, $callback)) {
            return $this;
        }

        foreach ($data as $part) {
            try {
                $this->$callback($part);
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                $this->_logger->logException($e);
            }
        }

        return $this;
    }

    /**
     * Import directories to storage
     *
     * @param  array $dirs
     * @return \Magento\Core\Model\File\Storage\File
     */
    public function importDirectories($dirs)
    {
        return $this->import($dirs, 'saveDir');
    }

    /**
     * Import files list
     *
     * @param  array $files
     * @return \Magento\Core\Model\File\Storage\File
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
        return $this->_getResource()->saveDir($dir);
    }

    /**
     * Save file to storage
     *
     * @param  array|\Magento\Core\Model\File\Storage\Database $file
     * @param  bool $overwrite
     * @throws \Magento\Core\Exception
     * @return bool|int
     */
    public function saveFile($file, $overwrite = true)
    {
        if (isset($file['filename']) && !empty($file['filename'])
            && isset($file['content']) && !empty($file['content'])
        ) {
            try {
                $filename = (isset($file['directory']) && !empty($file['directory']))
                    ? $file['directory'] . DS . $file['filename']
                    : $file['filename'];

                return $this->_getResource()
                    ->saveFile($filename, $file['content'], $overwrite);
            } catch (\Exception $e) {
                $this->_logger->logException($e);
                throw new \Magento\Core\Exception(
                    __('Unable to save file "%1" at "%2"', $file['filename'], $file['directory'])
                );
            }
        } else {
            throw new \Magento\Core\Exception(__('Wrong file info format'));
        }

        return false;
    }
}
