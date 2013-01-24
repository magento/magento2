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
 * @package     Mage_Backup
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup file item model
 *
 * @category   Mage
 * @package    Mage_Backup
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @method string getPath()
 * @method Mage_Backup_Model_Backup setPath() setPath($path)
 * @method string getName()
 * @method Mage_Backup_Model_Backup setName() setName($name)
 * @method string getTime()
 * @method Mage_Backup_Model_Backup setTime() setTime($time)
 */
class Mage_Backup_Model_Backup extends Varien_Object
{
    /* internal constants */
    const COMPRESS_RATE     = 9;

    /**
     * Type of backup file
     *
     * @var string
     */
    private $_type  = 'db';

    /**
     * Gz file pointer
     *
     * @var Magento_Filesystem_Stream_Zlib
     */
    protected $_stream = null;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * @var Mage_Backup_Helper_Data
     */
    protected $_helper;

    /**
     * @param Mage_Backup_Helper_Data $helper
     * @param array $data
     */
    public function __construct(Mage_Backup_Helper_Data $helper, $data = array())
    {
        $adapter = new Magento_Filesystem_Adapter_Zlib(self::COMPRESS_RATE);
        $this->_filesystem = new Magento_Filesystem($adapter);
        $this->_filesystem->setIsAllowCreateDirectories(true);
        $this->_helper = $helper;
        parent::__construct($data);
    }

    /**
     * Load backup file info
     *
     * @param string $fileName
     * @param string $filePath
     * @return Mage_Backup_Model_Backup
     */
    public function load($fileName, $filePath)
    {
        $backupData = $this->_helper->extractDataFromFilename($fileName);

        $this->addData(array(
            'id'   => $filePath . DS . $fileName,
            'time' => (int)$backupData->getTime(),
            'path' => $filePath,
            'extension' => $this->_helper->getExtensionByType($backupData->getType()),
            'display_name' => $this->_helper->nameToDisplayName($backupData->getName()),
            'name' => $backupData->getName(),
            'date_object' => new Zend_Date((int)$backupData->getTime(), Mage::app()->getLocale()->getLocaleCode())
        ));

        $this->setType($backupData->getType());
        return $this;
    }

    /**
     * Checks backup file exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->_filesystem->isFile($this->_getFilePath());
    }

    /**
     * Return file name of backup file
     *
     * @return string
     */
    public function getFileName()
    {
        $filename = $this->getTime() . "_" . $this->getType();
        $backupName = $this->getName();

        if (!empty($backupName)) {
            $filename .= '_' . $backupName;
        }

        $filename .= '.' . $this->_helper->getExtensionByType($this->getType());

        return $filename;
    }

    /**
     * Sets type of file
     *
     * @param string $value
     * @return Mage_Backup_Model_Backup
     */
    public function setType($value = 'db')
    {
        $possibleTypes = $this->_helper->getBackupTypesList();
        if (!in_array($value, $possibleTypes)) {
            $value = $this->_helper->getDefaultBackupType();
        }

        $this->_type = $value;
        $this->setData('type', $this->_type);

        return $this;
    }

    /**
     * Returns type of backup file
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set the backup file content
     *
     * @param string $content
     * @return Mage_Backup_Model_Backup
     */
    public function setFile(&$content)
    {
        if (!$this->hasData('time') || !$this->hasData('type') || !$this->hasData('path')) {
            Mage::throwException($this->_helper->__('Wrong order of creation for new backup.'));
        }

        $this->_filesystem->write($this->_getFilePath(), $content);
        return $this;
    }

    /**
     * Return content of backup file
     *
     * @return string
     */
    public function &getFile()
    {
        if (!$this->exists()) {
            Mage::throwException($this->_helper->__("Backup file does not exist."));
        }

        return $this->_filesystem->read($this->_getFilePath());
    }

    /**
     * Delete backup file
     *
     * @return Mage_Backup_Model_Backup
     */
    public function deleteFile()
    {
        if (!$this->exists()) {
            Mage::throwException($this->_helper->__("Backup file does not exist."));
        }

        $this->_filesystem->delete($this->_getFilePath());
        return $this;
    }

    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return Mage_Backup_Model_Backup
     * @throws Mage_Backup_Exception_NotEnoughPermissions
     */
    public function open($write = false)
    {
        if (is_null($this->getPath())) {
            Mage::exception('Mage_Backup', $this->_helper->__('Backup file path was not specified.'));
        }

        if ($write && $this->_filesystem->isFile($this->_getFilePath())) {
            $this->_filesystem->delete($this->_getFilePath());
        }
        if (!$write && !$this->_filesystem->isFile($this->_getFilePath())) {
            Mage::exception('Mage_Backup',
                $this->_helper->__('Backup file "%s" does not exist.', $this->getFileName()));
        }

        $mode = $write ? 'wb' . self::COMPRESS_RATE : 'rb';

        try {
            $compressStream = 'compress.zlib://';
            $workingDirectory = $this->_filesystem->getWorkingDirectory();
            $this->_stream = $this->_filesystem->createAndOpenStream($compressStream . $this->_getFilePath(), $mode,
                $compressStream . $workingDirectory);
        }
        catch (Magento_Filesystem_Exception $e) {
            throw new Mage_Backup_Exception_NotEnoughPermissions(
                $this->_helper->__('Backup file "%s" cannot be read from or written to.', $this->getFileName())
            );
        }

        return $this;
    }

    /**
     * Get zlib handler
     *
     * @return Magento_Filesystem_Stream_Zlib
     */
    protected function _getStream()
    {
        if (is_null($this->_stream)) {
            Mage::exception('Mage_Backup', $this->_helper->__('Backup file handler was unspecified.'));
        }
        return $this->_stream;
    }

    /**
     * Read backup uncomressed data
     *
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        return $this->_getStream()->read($length);
    }

    /**
     * Check end of file.
     *
     * @return bool
     */
    public function eof()
    {
        return $this->_getStream()->eof();
    }

    /**
     * Write to backup file
     *
     * @param string $string
     * @return Mage_Backup_Model_Backup
     */
    public function write($string)
    {
        try {
            $this->_getStream()->write($string);
        }
        catch (Magento_Filesystem_Exception $e) {
            Mage::exception('Mage_Backup',
                $this->_helper->__('An error occurred while writing to the backup file "%s".', $this->getFileName()));
        }

        return $this;
    }

    /**
     * Close open backup file
     *
     * @return Mage_Backup_Model_Backup
     */
    public function close()
    {
        $this->_getStream()->close();
        $this->_stream = null;

        return $this;
    }

    /**
     * Print output
     */
    public function output()
    {
        if (!$this->exists()) {
            return ;
        }

        $stream = $this->_filesystem->createAndOpenStream($this->_getFilePath(), 'r');
        while ($buffer = $stream->read(1024)) {
            echo $buffer;
        }
        $stream->close();
    }

    /**
     * @return int|mixed
     */
    public function getSize()
    {
        if (!is_null($this->getData('size'))) {
            return $this->getData('size');
        }

        if ($this->exists()) {
            $this->setData('size', $this->_filesystem->getFileSize($this->_getFilePath()));
            return $this->getData('size');
        }

        return 0;
    }

    /**
     * Validate user password
     *
     * @param string $password
     * @return bool
     */
    public function validateUserPassword($password)
    {
        $userPasswordHash = Mage::getModel('Mage_Backend_Model_Auth_Session')->getUser()->getPassword();
        return Mage::helper('Mage_Core_Helper_Data')->validateHash($password, $userPasswordHash);
    }

    /**
     * Load backup by it's type and creation timestamp
     *
     * @param int $timestamp
     * @param string $type
     * @return Mage_Backup_Model_Backup
     */
    public function loadByTimeAndType($timestamp, $type)
    {
        $backupsCollection = Mage::getSingleton('Mage_Backup_Model_Fs_Collection');
        $backupId = $timestamp . '_' . $type;

        foreach ($backupsCollection as $backup) {
            if ($backup->getId() == $backupId) {
                $this->setType($backup->getType())
                    ->setTime($backup->getTime())
                    ->setName($backup->getName())
                    ->setPath($backup->getPath());
                break;
            }
        }

        return $this;
    }

    /**
     * Get file path.
     *
     * @return string
     */
    protected function _getFilePath()
    {
        return $this->getPath() . DS . $this->getFileName();
    }
}
