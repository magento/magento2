<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Pdf_FileParserDataSource */
#require_once 'Zend/Pdf/FileParserDataSource.php';


/**
 * Concrete subclass of {@link Zend_Pdf_FileParserDataSource} that provides an
 * interface to filesystem objects.
 *
 * Note that this class cannot be used for other sources that may be supported
 * by {@link fopen()} (through URL wrappers). It may be used for local
 * filesystem objects only.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_FileParserDataSource_File extends Zend_Pdf_FileParserDataSource
{
  /**** Instance Variables ****/


    /**
     * Fully-qualified path to the file.
     * @var string
     */
    protected $_filePath = '';

    /**
     * File resource handle .
     * @var resource
     */
    protected $_fileResource = null;



  /**** Public Interface ****/


  /* Concrete Class Implementation */

    /**
     * Object constructor.
     *
     * Validates the path to the file, ensures that it is readable, then opens
     * it for reading.
     *
     * Throws an exception if the file is missing or cannot be opened.
     *
     * @param string $filePath Fully-qualified path to the file.
     * @throws Zend_Pdf_Exception
     */
    public function __construct($filePath)
    {
        if (! (is_file($filePath) || is_link($filePath))) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Invalid file path: $filePath",
                                         Zend_Pdf_Exception::BAD_FILE_PATH);
        }
        if (! is_readable($filePath)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("File is not readable: $filePath",
                                         Zend_Pdf_Exception::NOT_READABLE);
        }
        if (($this->_size = @filesize($filePath)) === false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Error while obtaining file size: $filePath",
                                         Zend_Pdf_Exception::CANT_GET_FILE_SIZE);
        }
        if (($this->_fileResource = @fopen($filePath, 'rb')) === false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Cannot open file for reading: $filePath",
                                         Zend_Pdf_Exception::CANT_OPEN_FILE);
        }
        $this->_filePath = $filePath;
    }

    /**
     * Object destructor.
     *
     * Closes the file if it had been successfully opened.
     */
    public function __destruct()
    {
        if (is_resource($this->_fileResource)) {
            @fclose($this->_fileResource);
        }
    }

    /**
     * Returns the specified number of raw bytes from the file at the byte
     * offset of the current read position.
     *
     * Advances the read position by the number of bytes read.
     *
     * Throws an exception if an error was encountered while reading the file or
     * if there is insufficient data to completely fulfill the request.
     *
     * @param integer $byteCount Number of bytes to read.
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function readBytes($byteCount)
    {
        $bytes = @fread($this->_fileResource, $byteCount);
        if ($bytes === false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Unexpected error while reading file',
                                         Zend_Pdf_Exception::ERROR_DURING_READ);
        }
        if (strlen($bytes) != $byteCount) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Insufficient data to read $byteCount bytes",
                                         Zend_Pdf_Exception::INSUFFICIENT_DATA);
        }
        $this->_offset += $byteCount;
        return $bytes;
    }

    /**
     * Returns the entire contents of the file as a string.
     *
     * Preserves the current file seek position.
     *
     * @return string
     */
    public function readAllBytes()
    {
        return file_get_contents($this->_filePath);
    }


  /* Object Magic Methods */

    /**
     * Returns the full filesystem path of the file.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->_filePath;
    }


  /* Primitive Methods */

    /**
     * Seeks the file read position to the specified byte offset.
     *
     * Throws an exception if the file pointer cannot be moved or if it is
     * moved beyond EOF (end of file).
     *
     * @param integer $offset Destination byte offset.
     * @throws Zend_Pdf_Exception
     */
    public function moveToOffset($offset)
    {
        if ($this->_offset == $offset) {
            return;    // Not moving; do nothing.
        }
        parent::moveToOffset($offset);
        $result = @fseek($this->_fileResource, $offset, SEEK_SET);
        if ($result !== 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Error while setting new file position',
                                         Zend_Pdf_Exception::CANT_SET_FILE_POSITION);
        }
        if (feof($this->_fileResource)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Moved beyond the end of the file',
                                         Zend_Pdf_Exception::MOVE_BEYOND_END_OF_FILE);
        }
    }

}
