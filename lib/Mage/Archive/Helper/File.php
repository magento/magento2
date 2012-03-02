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
 * @package     Mage_Archive
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
* Helper class that simplifies files stream reading and writing
*
* @category    Mage
* @package     Mage_Archive
* @author      Magento Core Team <core@magentocommerce.com>
*/
class Mage_Archive_Helper_File
{
    /**
     * Full path to directory where file located
     *
     * @var string
     */
    protected $_fileLocation;

    /**
     * File name
     *
     * @var string
     */
    protected $_fileName;

    /**
     * Full path (directory + filename) to file
     *
     * @var string
     */
    protected $_filePath;

    /**
     * File permissions that will be set if file opened in write mode
     *
     * @var int
     */
    protected $_chmod;

    /**
     * File handler
     *
     * @var pointer
     */
    protected $_fileHandler;

    /**
     * Set file path via constructor
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $pathInfo = pathinfo($filePath);

        $this->_filePath = $filePath;
        $this->_fileLocation = isset($pathInfo['dirname'])  ? $pathInfo['dirname'] : '';
        $this->_fileName     = isset($pathInfo['basename']) ? $pathInfo['basename'] : '';
    }

    /**
     * Close file if it's not closed before object destruction
     */
    public function __destruct()
    {
        if ($this->_fileHandler) {
            $this->_close();
        }
    }

    /**
     * Open file
     *
     * @param string $mode
     * @param int $chmod
     * @throws Mage_Exception
     */
    public function open($mode = 'w+', $chmod = 0666)
    {
        if ($this->_isWritableMode($mode)) {
            if (!is_writable($this->_fileLocation)) {
                throw new Mage_Exception('Permission denied to write to ' . $this->_fileLocation);
            }

            if (is_file($this->_filePath) && !is_writable($this->_filePath)) {
                throw new Mage_Exception("Can't open file " . $this->_fileName . " for writing. Permission denied.");
            }
        }

        if ($this->_isReadableMode($mode) && (!is_file($this->_filePath) || !is_readable($this->_filePath))) {
            if (!is_file($this->_filePath)) {
                throw new Mage_Exception('File ' . $this->_filePath . ' does not exist');
            }

            if (!is_readable($this->_filePath)) {
                throw new Mage_Exception('Permission denied to read file ' . $this->_filePath);
            }
        }

        $this->_open($mode);

        $this->_chmod = $chmod;
    }

    /**
     * Write data to file
     *
     * @param string $data
     */
    public function write($data)
    {
        $this->_checkFileOpened();
        $this->_write($data);
    }

    /**
     * Read data from file
     *
     * @param int $length
     * @return string|boolean
     */
    public function read($length = 4096)
    {
        $data = false;
        $this->_checkFileOpened();
        if ($length > 0) {
            $data = $this->_read($length);
        }

        return $data;
    }

    /**
     * Check whether end of file reached
     *
     * @return boolean
     */
    public function eof()
    {
        $this->_checkFileOpened();
        return $this->_eof();
    }

    /**
     * Close file
     */
    public function close()
    {
        $this->_checkFileOpened();
        $this->_close();
        $this->_fileHandler = false;
        @chmod($this->_filePath, $this->_chmod);
    }

    /**
     * Implementation of file opening
     *
     * @param string $mode
     * @throws Mage_Exception
     */
    protected function _open($mode)
    {
        $this->_fileHandler = @fopen($this->_filePath, $mode);

        if (false === $this->_fileHandler) {
            throw new Mage_Exception('Failed to open file ' . $this->_filePath);
        }
    }

    /**
     * Implementation of writing data to file
     *
     * @param string $data
     * @throws Mage_Exception
     */
    protected function _write($data)
    {
        $result = @fwrite($this->_fileHandler, $data);

        if (false === $result) {
            throw new Mage_Exception('Failed to write data to ' . $this->_filePath);
        }
    }

    /**
     * Implementation of file reading
     *
     * @param int $length
     * @throws Mage_Exception
     */
    protected function _read($length)
    {
        $result = fread($this->_fileHandler, $length);

        if (false === $result) {
            throw new Mage_Exception('Failed to read data from ' . $this->_filePath);
        }

        return $result;
    }

    /**
     * Implementation of EOF indicator
     *
     * @return boolean
     */
    protected function _eof()
    {
        return feof($this->_fileHandler);
    }

    /**
     * Implementation of file closing
     */
    protected function _close()
    {
        fclose($this->_fileHandler);
    }

    /**
     * Check whether requested mode is writable mode
     *
     * @param string $mode
     */
    protected function _isWritableMode($mode)
    {
        return preg_match('/(^[waxc])|(\+$)/', $mode);
    }

    /**
    * Check whether requested mode is readable mode
    *
    * @param string $mode
    */
    protected function _isReadableMode($mode) {
        return !$this->_isWritableMode($mode);
    }

    /**
     * Check whether file is opened
     *
     * @throws Mage_Exception
     */
    protected function _checkFileOpened()
    {
        if (!$this->_fileHandler) {
            throw new Mage_Exception('File not opened');
        }
    }
}
