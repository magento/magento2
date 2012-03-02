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
 * Directory database storage model class
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_File_Storage_Directory_Database extends Mage_Core_Model_File_Storage_Database_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'core_file_storage_directory_database';

    /**
     * Collect errors during sync process
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Class construct
     *
     * @param string $databaseConnection
     */
    public function __construct($connectionName = null)
    {
        $this->_init('Mage_Core_Model_Resource_File_Storage_Directory_Database');

        parent::__construct($connectionName);
    }

    /**
     * Load object data by path
     *
     * @param  string $path
     * @return Mage_Core_Model_File_Storage_Directory_Database
     */
    public function loadByPath($path)
    {
        /**
         * Clear model data
         * addData() is used because it's needed to clear only db storaged data
         */
        $this->addData(
            array(
                'directory_id'  => null,
                'name'          => null,
                'path'          => null,
                'upload_time'   => null,
                'parent_id'     => null
            )
        );

        $this->_getResource()->loadByPath($this, $path);
        return $this;
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
     * Retrieve directory parent id
     *
     * @return int
     */
    public function getParentId()
    {
        if (!$this->getData('parent_id')) {
            $parentId = $this->_getResource()->getParentId($this->getPath());
            if (empty($parentId)) {
                $parentId = null;
            }

            $this->setData('parent_id', $parentId);
        }

        return $parentId;
    }

    /**
     * Create directories recursively
     *
     * @param  string $path
     * @return Mage_Core_Model_File_Storage_Directory_Database
     */
    public function createRecursive($path)
    {
        $directory = Mage::getModel('Mage_Core_Model_File_Storage_Directory_Database')->loadByPath($path);

        if (!$directory->getId()) {
            $dirName = basename($path);
            $dirPath = dirname($path);

            if ($dirPath != '.') {
                $parentDir = $this->createRecursive($dirPath);
                $parentId = $parentDir->getId();
            } else {
                $dirPath = '';
                $parentId = null;
            }

            $directory->setName($dirName);
            $directory->setPath($dirPath);
            $directory->setParentId($parentId);
            $directory->save();
        }

        return $directory;
    }

    /**
     * Export directories from storage
     *
     * @param  int $offset
     * @param  int $count
     * @return bool
     */
    public function exportDirectories($offset = 0, $count = 100)
    {
        $offset = ((int) $offset >= 0) ? (int) $offset : 0;
        $count  = ((int) $count >= 1) ? (int) $count : 1;

        $result = $this->_getResource()->exportDirectories($offset, $count);

        if (empty($result)) {
            return false;
        }

        return $result;
    }

    /**
     * Import directories to storage
     *
     * @param  array $dirs
     * @return Mage_Core_Model_File_Storage_Directory_Database
     */
    public function importDirectories($dirs)
    {
        if (!is_array($dirs)) {
            return $this;
        }

        $dateSingleton = Mage::getSingleton('Mage_Core_Model_Date');
        foreach ($dirs as $dir) {
            if (!is_array($dir) || !isset($dir['name']) || !strlen($dir['name'])) {
                continue;
            }

            try {
                $directory = Mage::getModel(
                    'Mage_Core_Model_File_Storage_Directory_Database',
                    array('connection' => $this->getConnectionName())
                );
                $directory->setPath($dir['path']);

                $parentId = $directory->getParentId();
                if ($parentId || $dir['path'] == '') {
                    $directory->setName($dir['name']);
                    $directory->setUploadTime($dateSingleton->date());
                    $directory->save();
                } else {
                    Mage::throwException(Mage::helper('Mage_Core_Helper_Data')->__('Parent directory does not exist: %s', $dir['path']));
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $this;
    }

    /**
     * Clean directories at storage
     *
     * @return Mage_Core_Model_File_Storage_Directory_Database
     */
    public function clearDirectories()
    {
        $this->_getResource()->clearDirectories();
        return $this;
    }

    /**
     * Return subdirectories
     *
     * @param string $directory
     * @return mixed
     */
    public function getSubdirectories($directory)
    {
        $directory = Mage::helper('Mage_Core_Helper_File_Storage_Database')->getMediaRelativePath($directory);

        return $this->_getResource()->getSubdirectories($directory);
    }

    /**
     * Delete directory from database
     *
     * @param string $path
     * @return Mage_Core_Model_File_Storage_Directory_Database
     */
    public function deleteDirectory($dirPath)
    {
        $dirPath = Mage::helper('Mage_Core_Helper_File_Storage_Database')->getMediaRelativePath($dirPath);
        $name = basename($dirPath);
        $path = dirname($dirPath);

        if ('.' == $path) {
            $path = '';
        }

        $this->_getResource()->deleteDirectory($name, $path);

        return $this;
    }
}
