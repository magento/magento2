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
 * @version    $Id: MimeBodyString.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * A wrapper for strings for buffered reading.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_MimeBodyString
{

    /**
     * The source string.
     *
     * @var string
     */
    protected $_sourceString = '';

    /**
     * The size of the MIME message.
     * @var integer
     */
    protected $_bytesRead = 0;

    /**
     * Create a new MimeBodyString object.
     *
     * @param string $sourceString The string we are wrapping.
     */
    public function __construct($sourceString)
    {
        $this->_sourceString = $sourceString;
        $this->_bytesRead = 0;
    }

    /**
     * Read the next chunk of the string.
     *
     * @param integer $bytesRequested The size of the chunk that is to be read.
     * @return string A corresponding piece of the string.
     */
    public function read($bytesRequested)
    {
      $len = strlen($this->_sourceString);
      if($this->_bytesRead == $len) {
          return FALSE;
      } else if($bytesRequested > $len - $this->_bytesRead) {
          $bytesRequested = $len - $this->_bytesRead;
      }

      $buffer = substr($this->_sourceString, $this->_bytesRead, $bytesRequested);
      $this->_bytesRead += $bytesRequested;

      return $buffer;
    }

    /**
     * The length of the string.
     *
     * @return int The length of the string contained in the object.
     */
    public function getSize()
    {
      return strlen($this->_sourceString);
    }


}
