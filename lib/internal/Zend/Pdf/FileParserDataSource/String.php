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
 * @version    $Id: String.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/** Zend_Pdf_FileParserDataSource */
#require_once 'Zend/Pdf/FileParserDataSource.php';

/**
 * Concrete subclass of {@link Zend_Pdf_FileParserDataSource} that provides an
 * interface to binary strings.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_FileParserDataSource_String extends Zend_Pdf_FileParserDataSource
{
  /**** Instance Variables ****/


    /**
     * The string to parse.
     * @var string
     */
    protected $_string = '';



  /**** Public Interface ****/


  /* Concrete Class Implementation */

    /**
     * Object constructor.
     *
     * Verifies that the string is not empty.
     *
     * @param string $string String to parse.
     */
    public function __construct($string)
    {
        if (empty($string)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('String is empty',
                                         Zend_Pdf_Exception::PARAMETER_VALUE_OUT_OF_RANGE);
        }
        $this->_size = strlen($string);
        $this->_string = $string;
    }

    /**
     * Object destructor.
     */
    public function __destruct()
    {
        $this->_string = '';
    }

    /**
     * Returns the specified number of raw bytes from the string at the byte
     * offset of the current read position.
     *
     * Advances the read position by the number of bytes read.
     *
     * Throws an exception if there is insufficient data to completely fulfill
     * the request.
     *
     * @param integer $byteCount Number of bytes to read.
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public function readBytes($byteCount)
    {
        if (($this->_offset + $byteCount) > $this->_size) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Insufficient data to read $byteCount bytes",
                                         Zend_Pdf_Exception::INSUFFICIENT_DATA);
        }
        $bytes = substr($this->_string, $this->_offset, $byteCount);
        $this->_offset += $byteCount;
        return $bytes;
    }

    /**
     * Returns the entire string.
     *
     * Preserves the current read position.
     *
     * @return string
     */
    public function readAllBytes()
    {
        return $this->_string;
    }


  /* Object Magic Methods */

    /**
     * Returns a string containing the parsed string's length.
     *
     * @return string
     */
    public function __toString()
    {
        return "String ($this->_size bytes)";
    }
}
