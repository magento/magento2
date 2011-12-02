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
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: MediaFileSource.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_MediaData
 */
#require_once 'Zend/Gdata/App/BaseMediaSource.php';

/**
 * Concrete class to use a file handle as an attachment within a MediaEntry.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_MediaFileSource extends Zend_Gdata_App_BaseMediaSource
{
    /**
     * The filename which is represented
     *
     * @var string
     */
    protected $_filename = null;

    /**
     * The content type for the file attached (example image/png)
     *
     * @var string
     */
    protected $_contentType = null;

    /**
     * Create a new Zend_Gdata_App_MediaFileSource object.
     *
     * @param string $filename The name of the file to read from.
     */
    public function __construct($filename)
    {
        $this->setFilename($filename);
    }

    /**
     * Return the MIME multipart representation of this MediaEntry.
     *
     * @return string
     * @throws Zend_Gdata_App_IOException
     */
    public function encode()
    {
        if ($this->getFilename() !== null &&
            is_readable($this->getFilename())) {

            // Retrieves the file, using the include path
            $fileHandle = fopen($this->getFilename(), 'r', true);
            $result = fread($fileHandle, filesize($this->getFilename()));
            if ($result === false) {
                #require_once 'Zend/Gdata/App/IOException.php';
                throw new Zend_Gdata_App_IOException("Error reading file - " .
                        $this->getFilename() . '. Read failed.');
            }
            fclose($fileHandle);
            return $result;
        } else {
            #require_once 'Zend/Gdata/App/IOException.php';
            throw new Zend_Gdata_App_IOException("Error reading file - " .
                    $this->getFilename() . '. File is not readable.');
        }
    }

    /**
     * Get the filename associated with this reader.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Set the filename which is to be read.
     *
     * @param string $value The desired file handle.
     * @return Zend_Gdata_App_MediaFileSource Provides a fluent interface.
     */
    public function setFilename($value)
    {
        $this->_filename = $value;
        return $this;
    }

    /**
     * The content type for the file attached (example image/png)
     *
     * @return string The content type
     */
    public function getContentType()
    {
        return $this->_contentType;
    }

    /**
     * Set the content type for the file attached (example image/png)
     *
     * @param string $value The content type
     * @return Zend_Gdata_App_MediaFileSource Provides a fluent interface
     */
    public function setContentType($value)
    {
        $this->_contentType = $value;
        return $this;
    }

    /**
     * Alias for getFilename().
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFilename();
    }

}
