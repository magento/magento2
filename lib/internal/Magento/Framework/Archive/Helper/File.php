<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
* Helper class that simplifies files stream reading and writing
*/
namespace Magento\Framework\Archive\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;

class File
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
     * @var resource
     */
    protected $_fileHandler;

    /**
     * Whether file has been opened in write mode
     *
     * @var bool
     */
    protected $_isInWriteMode;

    /**
     * Set file path via constructor
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $pathInfo = pathinfo($filePath);

        $this->_filePath = $filePath;
        $this->_fileLocation = isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
        $this->_fileName = isset($pathInfo['basename']) ? $pathInfo['basename'] : '';
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
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function open($mode = 'w+', $chmod = null)
    {
        $this->_isInWriteMode = $this->_isWritableMode($mode);

        if ($this->_isInWriteMode) {
            if (!is_writable($this->_fileLocation)) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase('Permission denied to write to %1', [$this->_fileLocation])
                );
            }

            if (is_file($this->_filePath) && !is_writable($this->_filePath)) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase(
                        "Can't open file %1 for writing. Permission denied.",
                        [$this->_fileName]
                    )
                );
            }
        }

        if ($this->_isReadableMode($mode) && (!is_file($this->_filePath) || !is_readable($this->_filePath))) {
            if (!is_file($this->_filePath)) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase('File %1 does not exist', [$this->_filePath])
                );
            }

            if (!is_readable($this->_filePath)) {
                throw new LocalizedException(
                    new \Magento\Framework\Phrase('Permission denied to read file %1', [$this->_filePath])
                );
            }
        }

        $this->_open($mode);

        $this->_chmod = $chmod;
    }

    /**
     * Write data to file
     *
     * @param string $data
     * @return void
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
     *
     * @return void
     */
    public function close()
    {
        $this->_checkFileOpened();
        $this->_close();
        $this->_fileHandler = false;

        if ($this->_isInWriteMode && isset($this->_chmod)) {
            @chmod($this->_filePath, $this->_chmod);
        }
    }

    /**
     * Implementation of file opening
     *
     * @param string $mode
     * @return void
     * @throws LocalizedException
     */
    protected function _open($mode)
    {
        $this->_fileHandler = @fopen($this->_filePath, $mode);

        if (false === $this->_fileHandler) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Failed to open file %1', [$this->_filePath]));
        }
    }

    /**
     * Implementation of writing data to file
     *
     * @param string $data
     * @return void
     * @throws LocalizedException
     */
    protected function _write($data)
    {
        $result = @fwrite($this->_fileHandler, $data);

        if (false === $result) {
            throw new LocalizedException(new \Magento\Framework\Phrase('Failed to write data to %1', [$this->_filePath]));
        }
    }

    /**
     * Implementation of file reading
     *
     * @param int $length
     * @return string
     * @throws LocalizedException
     */
    protected function _read($length)
    {
        $result = fread($this->_fileHandler, $length);

        if (false === $result) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase('Failed to read data from %1', [$this->_filePath])
            );
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
     *
     * @return void
     */
    protected function _close()
    {
        fclose($this->_fileHandler);
    }

    /**
     * Check whether requested mode is writable mode
     *
     * @param string $mode
     * @return int
     */
    protected function _isWritableMode($mode)
    {
        return preg_match('/(^[waxc])|(\+$)/', $mode);
    }

    /**
     * Check whether requested mode is readable mode
     *
     * @param string $mode
     * @return bool
     */
    protected function _isReadableMode($mode)
    {
        return !$this->_isWritableMode($mode);
    }

    /**
     * Check whether file is opened
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _checkFileOpened()
    {
        if (!$this->_fileHandler) {
            throw new LocalizedException(new \Magento\Framework\Phrase('File not opened'));
        }
    }
}
