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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup file item model
 *
 * @category   Mage
 * @package    Mage_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backup_Model_Backup extends Varien_Object
{
    /* backup types */
    const BACKUP_DB     = 'db';
    const BACKUP_VIEW   = 'view';
    const BACKUP_MEDIA  = 'media';

    /* internal constants */
    const BACKUP_EXTENSION  = 'gz';
    const COMPRESS_RATE     = 9;

    /**
     * Type of backup file
     *
     * @var string db|media|view
     */
    private $_type  = 'db';

    /**
     * Gz file pointer
     *
     * @var resource
     */
    protected $_handler = null;

    /**
     * Load backup file info
     *
     * @param string fileName
     * @param string filePath
     * @return Mage_Backup_Model_Backup
     */
    public function load($fileName, $filePath)
    {
        list ($time, $type) = explode("_", substr($fileName, 0, strrpos($fileName, ".")));
        $this->addData(array(
            'id'   => $filePath . DS . $fileName,
            'time' => (int)$time,
            'path' => $filePath,
            'date_object' => new Zend_Date((int)$time)
        ));
        $this->setType($type);
        return $this;
    }

    /**
     * Checks backup file exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->getPath() . DS . $this->getFileName());
    }

    /**
     * Return file name of backup file
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->getTime() . "_" . $this->getType()
               . "." . self::BACKUP_EXTENSION;
    }

    /**
     * Sets type of file
     *
     * @param string $value db|media|view
     */
    public function setType($value='db')
    {
        if(!in_array($value, array('db','media','view'))) {
            $value = 'db';
        }

        $this->_type = $value;
        $this->setData('type', $this->_type);

        return $this;
    }

    /**
     * Returns type of backup file
     *
     * @return string db|media|view
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
     * @throws Mage_Backup_Exception
     */
    public function setFile(&$content)
    {
        if (!$this->hasData('time') || !$this->hasData('type') || !$this->hasData('path')) {
            Mage::throwException(Mage::helper('Mage_Backup_Helper_Data')->__('Wrong order of creation for new backup.'));
        }

        $ioProxy = new Varien_Io_File();
        $ioProxy->setAllowCreateFolders(true);
        $ioProxy->open(array('path'=>$this->getPath()));

        $compress = 0;
        if (extension_loaded("zlib")) {
            $compress = 1;
        }

        $rawContent = '';
        if ( $compress ) {
            $rawContent = gzcompress( $content, self::COMPRESS_RATE );
        } else {
            $rawContent = $content;
        }

        $fileHeaders = pack("ll", $compress, strlen($rawContent));
        $ioProxy->write($this->getFileName(), $fileHeaders . $rawContent);
        return $this;
    }

    /**
     * Return content of backup file
     *
     * @todo rewrite to Varien_IO, but there no possibility read part of files.
     * @return string
     * @throws Mage_Backup_Exception
     */
    public function &getFile()
    {

        if (!$this->exists()) {
            Mage::throwException(Mage::helper('Mage_Backup_Helper_Data')->__("Backup file does not exist."));
        }

        $fResource = @fopen($this->getPath() . DS . $this->getFileName(), "rb");
        if (!$fResource) {
            Mage::throwException(Mage::helper('Mage_Backup_Helper_Data')->__("Cannot read backup file."));
        }

        $content = '';
        $compressed = 0;

        $info = unpack("lcompress/llength", fread($fResource, 8));
        if ($info['compress']) { // If file compressed by zlib
            $compressed = 1;
        }

        if ($compressed && !extension_loaded("zlib")) {
            fclose($fResource);
            Mage::throwException(Mage::helper('Mage_Backup_Helper_Data')->__('The file was compressed with Zlib, but this extension is not installed on server.'));
        }

        if ($compressed) {
            $content = gzuncompress(fread($fResource, $info['length']));
        } else {
            $content = fread($fResource, $info['length']);
        }

        fclose($fResource);

        return $content;
    }

    /**
     * Delete backup file
     *
     * @throws Mage_Backup_Exception
     */
    public function deleteFile()
    {
        if (!$this->exists()) {
            Mage::throwException(Mage::helper('Mage_Backup_Helper_Data')->__("Backup file does not exist."));
        }

        $ioProxy = new Varien_Io_File();
        $ioProxy->open(array('path'=>$this->getPath()));
        $ioProxy->rm($this->getFileName());
        return $this;
    }

    /**
     * Open backup file (write or read mode)
     *
     * @param bool $write
     * @return Mage_Backup_Model_Backup
     */
    public function open($write = false)
    {
        if (is_null($this->getPath())) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('Backup file path was not specified.'));
        }

        $ioAdapter = new Varien_Io_File();
        try {
            $path = $ioAdapter->getCleanPath($this->getPath());
            $ioAdapter->checkAndCreateFolder($path);
            $filePath = $path . DS . $this->getFileName();
        }
        catch (Exception $e) {
            Mage::exception('Mage_Backup', $e->getMessage());
        }

        if ($write && $ioAdapter->fileExists($filePath)) {
            $ioAdapter->rm($filePath);
        }
        if (!$write && !$ioAdapter->fileExists($filePath)) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('Backup file "%s" does not exist.', $this->getFileName()));
        }

        $mode = $write ? 'wb' . self::COMPRESS_RATE : 'rb';

        try {
            $this->_handler = gzopen($filePath, $mode);
        }
        catch (Exception $e) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('Backup file "%s" cannot be read from or written to.', $this->getFileName()));
        }

        return $this;
    }

    /**
     * Read backup uncomressed data
     *
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        if (is_null($this->_handler)) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('Backup file handler was unspecified.'));
        }

        return gzread($this->_handler, $length);
    }

    public function eof()
    {
        if (is_null($this->_handler)) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('Backup file handler was unspecified.'));
        }

        return gzeof($this->_handler);
    }

    /**
     * Write to backup file
     *
     * @param string $string
     * @return Mage_Backup_Model_Backup
     */
    public function write($string)
    {
        if (is_null($this->_handler)) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('Backup file handler was unspecified.'));
        }

        try {
            gzwrite($this->_handler, $string);
        }
        catch (Exception $e) {
            Mage::exception('Mage_Backup', Mage::helper('Mage_Backup_Helper_Data')->__('An error occurred while writing to the backup file "%s".', $this->getFileName()));
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
        @gzclose($this->_handler);
        $this->_handler = null;

        return $this;
    }

    /**
     * Print output
     *
     */
    public function output()
    {
        if (!$this->exists()) {
            return ;
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => $this->getPath()));

        $ioAdapter->streamOpen($this->getFileName(), 'r');
        while ($buffer = $ioAdapter->streamRead()) {
            echo $buffer;
        }
        $ioAdapter->streamClose();
    }

    public function getSize()
    {
        if (!is_null($this->getData('size'))) {
            return $this->getData('size');
        }

        if ($this->exists()) {
            $this->setData('size', filesize($this->getPath() . DS . $this->getFileName()));
            return $this->getData('size');
        }

        return 0;
    }
}
