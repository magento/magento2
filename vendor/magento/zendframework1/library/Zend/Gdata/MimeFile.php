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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * A wrapper for strings for buffered reading.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Gdata
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_MimeFile
{

    /**
     * A handle to the file that is part of the message.
     *
     * @var resource
     */
    protected $_fileHandle = null;

    /**
     * Create a new MimeFile object.
     *
     * @param string $fileHandle An open file handle to the file being
     *               read.
     */
    public function __construct($fileHandle)
    {
        $this->_fileHandle = $fileHandle;
    }

    /**
     * Read the next chunk of the file.
     *
     * @param integer $bytesRequested The size of the chunk that is to be read.
     * @return string A corresponding piece of the message. This could be
     *                binary or regular text.
     */
    public function read($bytesRequested)
    {
      return fread($this->_fileHandle, $bytesRequested);
    }

}
