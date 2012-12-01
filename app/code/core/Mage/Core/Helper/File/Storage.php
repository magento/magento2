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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * File storage helper
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Helper_File_Storage extends Mage_Core_Helper_Abstract
{
    /**
     * Maximum file size for MAX_FILE_SIZE attribute of a form
     *
     * @link http://www.php.net/manual/en/features.file-upload.post-method.php
     * @var integer
     */
    protected static $_maxFileSize = -1;

    /**
     * Current storage code
     *
     * @var int
     */
    protected $_currentStorage = null;

    /**
     * List of internal storages
     *
     * @var array
     */
    protected $_internalStorageList = array(
        Mage_Core_Model_File_Storage::STORAGE_MEDIA_FILE_SYSTEM
    );

    /**
     * Return saved storage code
     *
     * @return int
     */
    public function getCurrentStorageCode()
    {
        if (is_null($this->_currentStorage)) {
            $this->_currentStorage = (int) Mage::app()
                ->getConfig()->getNode(Mage_Core_Model_File_Storage::XML_PATH_STORAGE_MEDIA);
        }

        return $this->_currentStorage;
    }

    /**
     * Retrieve file system storage model
     *
     * @return Mage_Core_Model_File_Storage_File
     */
    public function getStorageFileModel()
    {
        return Mage::getSingleton('Mage_Core_Model_File_Storage_File');
    }

    /**
     * Check if storage is internal
     *
     * @param  int|null $storage
     * @return bool
     */
    public function isInternalStorage($storage = null)
    {
        $storage = (!is_null($storage)) ? (int) $storage : $this->getCurrentStorageCode();

        return in_array($storage, $this->_internalStorageList);
    }

    /**
     * Retrieve storage model
     *
     * @param  int|null $storage
     * @param  array $params
     * @return Mage_Core_Model_Abstract|bool
     */
    public function getStorageModel($storage = null, $params = array())
    {
        return Mage::getSingleton('Mage_Core_Model_File_Storage')->getStorageModel($storage, $params);
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

        $dbHelper = Mage::helper('Mage_Core_Helper_File_Storage_Database');

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
     * @param  Mage_Core_Model_File_Storage_Database $file
     * @return bool|int
     */
    public function saveFileToFileSystem($file)
    {
        return $this->getStorageFileModel()->saveFile($file, true);
    }

    /**
     * Get post max size
     *
     * @return string
     */
    public function getPostMaxSize()
    {
        return $this->_iniGet('post_max_size');
    }

    /**
     * Get upload max size
     *
     * @return string
     */
    public function getUploadMaxSize()
    {
        return $this->_iniGet('upload_max_filesize');
    }

    /**
     * Gets the value of a configuration option
     *
     * @link http://php.net/manual/en/function.ini-get.php
     * @param string $param The configuration option name
     * @return string
     */
    protected function _iniGet($param)
    {
        return trim(ini_get($param));
    }

    /**
     * Get max file size in megabytes
     *
     * @param int $precision
     * @param int $mode
     * @return float
     */
    public function getMaxFileSizeInMb($precision = 0, $mode = PHP_ROUND_HALF_DOWN)
    {
        return round($this->getMaxFileSize() / (1024 * 1024), $precision, $mode);
    }

    /**
     * Get the maximum file size of the a form in bytes
     *
     * @return integer
     */
    public function getMaxFileSize()
    {
        if (self::$_maxFileSize < 0) {
            $postMaxSize = $this->_convertIniToInteger($this->getPostMaxSize());
            $uploadMaxSize = $this->_convertIniToInteger($this->getUploadMaxSize());
            $min = max($postMaxSize, $uploadMaxSize);

            if ($postMaxSize > 0) {
                $min = min($min, $postMaxSize);
            }

            if ($uploadMaxSize > 0) {
                $min = min($min, $uploadMaxSize);
            }

            self::$_maxFileSize = $min;
        }

        return self::$_maxFileSize;
    }

    /**
     * Converts a ini setting to a integer value
     *
     * @param string $setting
     * @return integer
     */
    protected function _convertIniToInteger($setting)
    {
        if (!is_numeric($setting)) {
            $type = strtoupper(substr($setting, -1));
            $setting = (integer)$setting;

            switch ($type) {
                case 'K':
                    $setting *= 1024;
                    break;

                case 'M':
                    $setting *= 1024 * 1024;
                    break;

                case 'G':
                    $setting *= 1024 * 1024 * 1024;
                    break;

                default:
                    break;
            }
        }
        return (integer)$setting;
    }
}
