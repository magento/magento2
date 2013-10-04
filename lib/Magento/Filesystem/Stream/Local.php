<?php
/**
 * Magento filesystem local stream
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Stream;

class Local implements \Magento\Filesystem\StreamInterface
{
    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * Stream path
     *
     * @var string
     */
    protected $_path;

    /**
     * Stream mode
     *
     * @var \Magento\Filesystem\Stream\Mode
     */
    protected $_mode;

    /**
     * Stream file resource handle
     *
     * @var
     */
    protected $_fileHandle;

    /**
     * Is stream locked
     *
     * @var bool
     */
    protected $_isLocked = false;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * Opens the stream in the specified mode
     *
     * @param \Magento\Filesystem\Stream\Mode|string $mode
     * @throws \Magento\Filesystem\FilesystemException If stream cannot be opened
     */
    public function open($mode)
    {
        if (is_string($mode)) {
            $mode = new \Magento\Filesystem\Stream\Mode($mode);
        }
        $fileHandle = @fopen($this->_path, $mode->getMode());
        if (false === $fileHandle) {
            throw new \Magento\Filesystem\FilesystemException(
                sprintf('The stream "%s" cannot be opened', $this->_path)
            );
        }
        $this->_mode = $mode;
        $this->_fileHandle = $fileHandle;
    }

    /**
     * Reads the specified number of bytes from the current position.
     *
     * @param integer $count The number of bytes to read
     * @return string
     * @throws \Magento\Filesystem\FilesystemException If stream wasn't read.
     */
    public function read($count)
    {
        $this->_assertReadable();
        $result = @fread($this->_fileHandle, $count);
        if ($result === false) {
            throw new \Magento\Filesystem\FilesystemException('Read of the stream caused an error.');
        }
        return $result;
    }

    /**
     * Reads one CSV row from the stream
     *
     * @param int $count [optional] <p>
     * Must be greater than the longest line (in characters) to be found in
     * the CSV file (allowing for trailing line-end characters). It became
     * optional in PHP 5. Omitting this parameter (or setting it to 0 in PHP
     * 5.0.4 and later) the maximum line length is not limited, which is
     * slightly slower.
     * @param string $delimiter
     * @param string $enclosure
     * @return array|bool false on end of file
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function readCsv($count = 0, $delimiter = ',', $enclosure = '"')
    {
        $this->_assertReadable();
        $result = @fgetcsv($this->_fileHandle, $count);
        if ($result === false && $this->eof()) {
            return false;
        }
        if (!is_array($result)) {
            throw new \Magento\Filesystem\FilesystemException('Read of the stream caused an error.');
        }
        return $result;
    }

    /**
     * Writes the data to stream.
     *
     * @param string $data
     * @return integer
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function write($data)
    {
        $this->_assertWritable();
        $result = @fwrite($this->_fileHandle, $data);
        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException('Write to the stream caused an error.');
        }
        return $result;
    }

    /**
     * Writes one CSV row to the stream.
     *
     * @param array $data
     * @param string $delimiter
     * @param string $enclosure
     * @return integer
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function writeCsv(array $data, $delimiter = ',', $enclosure = '"')
    {
        $this->_assertWritable();
        $result = fputcsv($this->_fileHandle, $data, $delimiter, $enclosure);
        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException('Write to the stream caused an error.');
        }
        return $result;
    }

    /**
     * Closes the stream.
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function close()
    {
        $this->_assertOpened();
        if ($this->_isLocked) {
            $this->unlock();
        }
        $result = @fclose($this->_fileHandle);

        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException('Close of the stream caused an error.');
        }

        $this->_mode = null;
        $this->_fileHandle = null;
    }

    /**
     * Flushes the output.
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function flush()
    {
        $this->_assertOpened();
        $result = @fflush($this->_fileHandle);
        if (!$result) {
            throw new \Magento\Filesystem\FilesystemException('Flush of the stream caused an error.');
        }
    }

    /**
     * Seeks to the specified offset
     *
     * @param int $offset
     * @param int $whence
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->_assertOpened();
        $result = fseek($this->_fileHandle, $offset, $whence);
        if (0 !== $result) {
            throw new \Magento\Filesystem\FilesystemException('seek operation on the stream caused an error.');
        }
    }

    /**
     * Returns the current position
     *
     * @return int
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function tell()
    {
        $this->_assertOpened();
        $result = ftell($this->_fileHandle);
        if (false === $result) {
            throw new \Magento\Filesystem\FilesystemException('tell operation on the stream caused an error.');
        }
        return $result;
    }

    /**
     * Checks if the current position is the end-of-file
     *
     * @return bool
     */
    public function eof()
    {
        $this->_assertOpened();
        return (bool)@feof($this->_fileHandle);
    }

    /**
     * Asserts the stream is readable
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function _assertReadable()
    {
        $this->_assertOpened();
        if (false === $this->_mode->isReadAllowed()) {
            throw new \Magento\Filesystem\FilesystemException('The stream does not allow read.');
        }
    }

    /**
     * Asserts the stream is writable
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function _assertWritable()
    {
        $this->_assertOpened();
        if (false === $this->_mode->isWriteAllowed()) {
            throw new \Magento\Filesystem\FilesystemException('The stream does not allow write.');
        }
    }

    /**
     * Asserts the stream is opened
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    protected function _assertOpened()
    {
        if (!$this->_fileHandle) {
            throw new \Magento\Filesystem\FilesystemException(sprintf('The stream "%s" is not opened', $this->_path));
        }
    }

    /**
     * Portable advisory file locking
     *
     * @param bool $exclusive
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function lock($exclusive = true)
    {
        $this->_assertOpened();
        $lock = $exclusive ? LOCK_EX : LOCK_SH;
        $this->_isLocked = flock($this->_fileHandle, $lock);
        if (!$this->_isLocked) {
            throw new \Magento\Filesystem\FilesystemException(
                sprintf('The stream "%s" can not be locked', $this->_path)
            );
        }
    }

    /**
     * File unlocking
     *
     * @throws \Magento\Filesystem\FilesystemException
     */
    public function unlock()
    {
        $this->_assertOpened();
        if ($this->_isLocked) {
            if (!flock($this->_fileHandle, LOCK_UN)) {
                throw new \Magento\Filesystem\FilesystemException(
                    sprintf('The stream "%s" can not be unlocked', $this->_path)
                );
            }
        }
        $this->_isLocked = false;
    }
}
