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
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MediaMimeStream.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
*  @see Zend_Gdata_MimeFile
*/
#require_once 'Zend/Gdata/MimeFile.php';

/**
* @see Zend_Gdata_MimeBodyString
*/
#require_once 'Zend/Gdata/MimeBodyString.php';


/**
 * A streaming Media MIME class that allows for buffered read operations.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_MediaMimeStream
{

    /**
     * A valid MIME boundary.
     *
     * @var string
     */
    protected $_boundaryString = null;

    /**
     * A handle to the file that is part of the message.
     *
     * @var resource
     */
    protected $_fileHandle = null;

    /**
     * The current part being read from.
     * @var integer
     */
    protected $_currentPart = 0;

    /**
     * The size of the MIME message.
     * @var integer
     */
    protected $_totalSize = 0;

    /**
     * An array of all the parts to be sent. Array members are either a
     * MimeFile or a MimeBodyString object.
     * @var array
     */
    protected $_parts = null;

    /**
     * Create a new MimeMediaStream object.
     *
     * @param string $xmlString The string corresponding to the XML section
     *               of the message, typically an atom entry or feed.
     * @param string $filePath The path to the file that constitutes the binary
     *               part of the message.
     * @param string $fileContentType The valid internet media type of the file.
     * @throws Zend_Gdata_App_IOException If the file cannot be read or does
     *         not exist. Also if mbstring.func_overload has been set > 1.
     */
    public function __construct($xmlString = null, $filePath = null,
        $fileContentType = null)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            #require_once 'Zend/Gdata/App/IOException.php';
            throw new Zend_Gdata_App_IOException('File to be uploaded at ' .
                $filePath . ' does not exist or is not readable.');
        }

        $this->_fileHandle = fopen($filePath, 'rb', TRUE);
        $this->_boundaryString = '=_' . md5(microtime(1) . rand(1,20));
        $entry = $this->wrapEntry($xmlString, $fileContentType);
        $closingBoundary = new Zend_Gdata_MimeBodyString("\r\n--{$this->_boundaryString}--\r\n");
        $file = new Zend_Gdata_MimeFile($this->_fileHandle);
        $this->_parts = array($entry, $file, $closingBoundary);

        $fileSize = filesize($filePath);
        $this->_totalSize = $entry->getSize() + $fileSize
          + $closingBoundary->getSize();

    }

    /**
     * Sandwiches the entry body into a MIME message
     *
     * @return void
     */
    private function wrapEntry($entry, $fileMimeType)
    {
        $wrappedEntry = "--{$this->_boundaryString}\r\n";
        $wrappedEntry .= "Content-Type: application/atom+xml\r\n\r\n";
        $wrappedEntry .= $entry;
        $wrappedEntry .= "\r\n--{$this->_boundaryString}\r\n";
        $wrappedEntry .= "Content-Type: $fileMimeType\r\n\r\n";
        return new Zend_Gdata_MimeBodyString($wrappedEntry);
    }

    /**
     * Read a specific chunk of the the MIME multipart message.
     *
     * @param integer $bufferSize The size of the chunk that is to be read,
     *                            must be lower than MAX_BUFFER_SIZE.
     * @return string A corresponding piece of the message. This could be
     *                binary or regular text.
     */
    public function read($bytesRequested)
    {
        if($this->_currentPart >= count($this->_parts)) {
          return FALSE;
        }

        $activePart = $this->_parts[$this->_currentPart];
        $buffer = $activePart->read($bytesRequested);

        while(strlen($buffer) < $bytesRequested) {
          $this->_currentPart += 1;
          $nextBuffer = $this->read($bytesRequested - strlen($buffer));
          if($nextBuffer === FALSE) {
            break;
          }
          $buffer .= $nextBuffer;
        }

        return $buffer;
    }

    /**
     * Return the total size of the mime message.
     *
     * @return integer Total size of the message to be sent.
     */
    public function getTotalSize()
    {
        return $this->_totalSize;
    }

    /**
     * Close the internal file that we are streaming to the socket.
     *
     * @return void
     */
    public function closeFileHandle()
    {
        if ($this->_fileHandle !== null) {
            fclose($this->_fileHandle);
        }
    }

    /**
     * Return a Content-type header that includes the current boundary string.
     *
     * @return string A valid HTTP Content-Type header.
     */
    public function getContentType()
    {
        return 'multipart/related;boundary="' .
            $this->_boundaryString . '"' . "\r\n";
    }

}
