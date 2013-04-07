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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Model for synchronization from DB to filesystem
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_File_Storage_File
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_mediaBaseDirectory = null;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Core_Helper_File_Storage_Database
     */
    protected $_dbHelper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @param Magento_Filesystem $filesystem
     * @param Mage_Core_Helper_File_Storage_Database $dbHelper
     * @param Mage_Core_Helper_Data $helper
     * @param Mage_Core_Model_Logger $log
     */
    public function __construct(
        Magento_Filesystem $filesystem,
        Mage_Core_Helper_File_Storage_Database $dbHelper,
        Mage_Core_Helper_Data $helper,
        Mage_Core_Model_Logger $log
    ) {
        $this->_dbHelper = $dbHelper;
        $this->_helper = $helper;
        $this->_logger = $log;

        $this->_filesystem = $filesystem;
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_filesystem->ensureDirectoryExists($this->getMediaBaseDirectory());
        $this->_filesystem->setWorkingDirectory($this->getMediaBaseDirectory());
    }

    /**
     * Files at storage
     *
     * @return string
     */
    public function getMediaBaseDirectory()
    {
        if (is_null($this->_mediaBaseDirectory)) {
            $this->_mediaBaseDirectory = $this->_dbHelper->getMediaBaseDir();
        }

        return $this->_mediaBaseDirectory;
    }

    /**
     * Collect files and directories recursively
     *
     * @param string $dir
     * @return array
     */
    public function getStorageData($dir = '')
    {
        $files          = array();
        $directories    = array();
        $currentDir     = $this->getMediaBaseDirectory() . $dir;

        if ($this->_filesystem->isDirectory($currentDir)) {
            foreach ($this->_filesystem->getNestedKeys($currentDir) as $fullPath) {
                $itemName = basename($fullPath);
                if ($itemName == '.svn' || $itemName == '.htaccess') {
                    continue;
                }

                $relativePath = $this->_getRelativePath($fullPath);
                if ($this->_filesystem->isDirectory($fullPath)) {
                    $directories[] = array(
                        'name' => $itemName,
                        'path' => dirname($relativePath)
                    );
                } else {
                    $files[] = $relativePath;
                }
            }
        }

        return array('files' => $files, 'directories' => $directories);
    }

    /**
     * Clear files and directories in storage
     *
     * @param string $dir
     * @return Mage_Core_Model_Resource_File_Storage_File
     */
    public function clear($dir = '')
    {
        if (strpos($dir, $this->getMediaBaseDirectory()) !== 0) {
            $dir = $this->getMediaBaseDirectory() . $dir;
        }

        if ($this->_filesystem->isDirectory($dir)) {
            foreach ($this->_filesystem->getNestedKeys($dir) as $path) {
                $this->_filesystem->delete($path);
            }
        }

        return $this;
    }

    /**
     * Save directory to storage
     *
     * @param array $dir
     * @return bool
     */
    public function saveDir($dir)
    {
        if (!isset($dir['name']) || !strlen($dir['name']) || !isset($dir['path'])) {
            return false;
        }

        $path = (strlen($dir['path']))
            ? $dir['path'] . DS . $dir['name']
            : $dir['name'];
        $path = $this->getMediaBaseDirectory() . DS . $path;

        try {
            $this->_filesystem->ensureDirectoryExists($path);
        } catch (Exception $e) {
            $this->_logger->log($e->getMessage());
            Mage::throwException($this->_helper->__('Unable to create directory: %s', $path));
        }

        return true;
    }

    /**
     * Save file to storage
     *
     * @param string $filePath
     * @param string $content
     * @param bool $overwrite
     * @return bool
     */
    public function saveFile($filePath, $content, $overwrite = false)
    {
        if (strpos($filePath, $this->getMediaBaseDirectory()) !== 0) {
            $filePath = $this->getMediaBaseDirectory() . DS . $filePath;
        }

        try {
            if (!$this->_filesystem->isFile($filePath) || ($overwrite && $this->_filesystem->delete($filePath))) {
                $this->_filesystem->write($filePath, $content);
                return true;
            }
        } catch (Magento_Filesystem_Exception $e) {
            $this->_logger->log($e->getMessage());
            Mage::throwException($this->_helper->__('Unable to save file: %s', $filePath));
        }

        return false;
    }

    /**
     * Get path relative to media base directory
     *
     * @param string $path
     * @return string
     */
    protected function _getRelativePath($path)
    {
        return ltrim(str_replace($this->getMediaBaseDirectory(), '', $path), Magento_Filesystem::DIRECTORY_SEPARATOR);
    }
}
