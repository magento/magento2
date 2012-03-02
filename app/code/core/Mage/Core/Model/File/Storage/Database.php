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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * File storage database model class
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_File_Storage_Database extends Mage_Core_Model_File_Storage_Database_Abstract
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
     * @var Mage_Core_Model_File_Storage_Directory_Database
     */
    protected $_directoryModel = null;

    /**
     * Collect errors during sync process
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Class construct
     *
     * @param string $connectionName
     */
    public function __construct($connectionName = null)
    {
        $this->_init('Mage_Core_Model_Resource_File_Storage_Database');

        parent::__construct($connectionName);
    }

    /**
     * Retrieve directory model
     *
     * @return Mage_Core_Model_File_Storage_Directory_Database
     */
    public function getDirectoryModel()
    {
        if (is_null($this->_directoryModel)) {
            $this->_directoryModel = Mage::getModel(
                'Mage_Core_Model_File_Storage_Directory_Database',
                array('connection' => $this->getConnectionName()));
        }

        return $this->_directoryModel;
    }

    /**
     * Create tables for file and directory storages
     *
     * @return Mage_Core_Model_File_Storage_Database
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
        return Mage::helper('Mage_Core_Helper_Data')->__('database "%s"', $this->getConnectionName());
    }

    /**
     * Load object data by filename
     *
     * @param  string $filePath
     * @return Mage_Core_Model_File_Storage_Database
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
     * @return Mage_Core_Model_File_Storage_Database
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
     * @return Mage_Core_Model_File_Storage_Directory_Database
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
     * @return Mage_Core_Model_File_Storage_Database
     */
    public function importFiles($files)
    {
        if (!is_array($files)) {
            return $this;
        }

        $dateSingleton = Mage::getSingleton('Mage_Core_Model_Date');
        foreach ($files as $file) {
            if (!isset($file['filename']) || !strlen($file['filename']) || !isset($file['content'])) {
                continue;
            }

            try {
                $file['update_time'] = $dateSingleton->date();
                $file['directory_id'] = (isset($file['directory']) && strlen($file['directory']))
                    ? Mage::getModel(
                        'Mage_Core_Model_File_Storage_Directory_Database',
                        array('connection' => $this->getConnectionName()))
                            ->loadByPath($file['directory'])->getId()
                    : null;

                $this->_getResource()->saveFile($file);
            } catch (Exception $e) {
                $this->_errors[] = $e->getMessage();
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Store file into database
     *
     * @param  string $filename
     * @return Mage_Core_Model_File_Storage_Database
     */
    public function saveFile($filename)
    {
        $fileInfo = $this->collectFileInfo($filename);
        $filePath = $fileInfo['directory'];

        $directory = Mage::getModel('Mage_Core_Model_File_Storage_Directory_Database')->loadByPath($filePath);

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
     * @return Mage_Core_Model_File_Storage_Database
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
     * @return Mage_Core_Model_File_Storage_Database
     */
    public function renameFile($oldFilePath, $newFilePath)
    {
        $this->_getResource()->renameFile(
            basename($oldFilePath),
            dirname($oldFilePath),
            basename($newFilePath),
            dirname($newFilePath)
        );

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
        $directory = Mage::helper('Mage_Core_Helper_File_Storage_Database')->getMediaRelativePath($directory);
        return $this->_getResource()->getDirectoryFiles($directory);
    }

    /**
     * Delete file from database
     *
     * @param string $path
     * @return Mage_Core_Model_File_Storage_Database
     */
    public function deleteFile($path)
    {
        $filename = basename($path);
        $directory = dirname($path);
        $this->_getResource()->deleteFile($filename, $directory);

        return $this;
    }
}
