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
 * @version    $Id: FileParserDataSource.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Abstract helper class for {@link Zend_Pdf_FileParser} that provides the
 * data source for parsing.
 *
 * Concrete subclasses allow for parsing of in-memory, filesystem, and other
 * sources through a common API. These subclasses also take care of error
 * handling and other mundane tasks.
 *
 * Subclasses must implement at minimum {@link __construct()},
 * {@link __destruct()}, {@link readBytes()}, and {@link readAllBytes()}.
 * Subclasses should also override {@link moveToOffset()} and
 * {@link __toString()} as appropriate.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_FileParserDataSource
{
  /**** Instance Variables ****/


    /**
     * Total size in bytes of the data source.
     * @var integer
     */
    protected $_size = 0;

    /**
     * Byte offset of the current read position within the data source.
     * @var integer
     */
    protected $_offset = 0;



  /**** Public Interface ****/


  /* Abstract Methods */

    /**
     * Object constructor. Opens the data source for parsing.
     *
     * Must set $this->_size to the total size in bytes of the data source.
     *
     * Upon return the data source can be interrogated using the primitive
     * methods described here.
     *
     * If the data source cannot be opened for any reason (such as insufficient
     * permissions, missing file, etc.), will throw an appropriate exception.
     *
     * @throws Zend_Pdf_Exception
     */
    abstract public function __construct();

    /**
     * Object destructor. Closes the data source.
     *
     * May also perform cleanup tasks such as deleting temporary files.
     */
    abstract public function __destruct();

    /**
     * Returns the specified number of raw bytes from the data source at the
     * byte offset of the current read position.
     *
     * Must advance the read position by the number of bytes read by updating
     * $this->_offset.
     *
     * Throws an exception if there is insufficient data to completely fulfill
     * the request or if an error occurs.
     *
     * @param integer $byteCount Number of bytes to read.
     * @return string
     * @throws Zend_Pdf_Exception
     */
    abstract public function readBytes($byteCount);

    /**
     * Returns the entire contents of the data source as a string.
     *
     * This method may be called at any time and so must preserve the byte
     * offset of the read position, both through $this->_offset and whatever
     * other additional pointers (such as the seek position of a file pointer)
     * that might be used.
     *
     * @return string
     */
    abstract public function readAllBytes();


  /* Object Magic Methods */

    /**
     * Returns a description of the object for debugging purposes.
     *
     * Subclasses should override this method to provide a more specific
     * description of the actual object being represented.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }


  /* Accessors */

    /**
     * Returns the byte offset of the current read position within the data
     * source.
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * Returns the total size in bytes of the data source.
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->_size;
    }


  /* Primitive Methods */

    /**
     * Moves the current read position to the specified byte offset.
     *
     * Throws an exception you attempt to move before the beginning or beyond
     * the end of the data source.
     *
     * If a subclass needs to perform additional tasks (such as performing a
     * fseek() on a filesystem source), it should do so after calling this
     * parent method.
     *
     * @param integer $offset Destination byte offset.
     * @throws Zend_Pdf_Exception
     */
    public function moveToOffset($offset)
    {
        if ($this->_offset == $offset) {
            return;    // Not moving; do nothing.
        }
        if ($offset < 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Attempt to move before start of data source',
                                         Zend_Pdf_Exception::MOVE_BEFORE_START_OF_FILE);
        }
        if ($offset >= $this->_size) {    // Offsets are zero-based.
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Attempt to move beyond end of data source',
                                         Zend_Pdf_Exception::MOVE_BEYOND_END_OF_FILE);
        }
        $this->_offset = $offset;
    }

    /**
     * Shifts the current read position within the data source by the specified
     * number of bytes.
     *
     * You may move forward (positive numbers) or backward (negative numbers).
     * Throws an exception you attempt to move before the beginning or beyond
     * the end of the data source.
     *
     * @param integer $byteCount Number of bytes to skip.
     * @throws Zend_Pdf_Exception
     */
    public function skipBytes($byteCount)
    {
        $this->moveToOffset($this->_offset + $byteCount);
    }
}
