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
 * @version    $Id: MediaEntry.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_App_Entry
 */
#require_once 'Zend/Gdata/App/Entry.php';

/**
 * @see Zend_Gdata_App_MediaSource
 */
#require_once 'Zend/Gdata/App/MediaSource.php';

/**
 * @see Zend_Gdata_MediaMimeStream
 */
#require_once 'Zend/Gdata/MediaMimeStream.php';

/**
 * Concrete class for working with Atom entries containing multi-part data.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_MediaEntry extends Zend_Gdata_App_Entry
{
    /**
     * The attached MediaSource/file
     *
     * @var Zend_Gdata_App_MediaSource
     */
    protected $_mediaSource = null;

    /**
     * Constructs a new MediaEntry, representing XML data and optional
     * file to upload
     *
     * @param DOMElement $element (optional) DOMElement from which this
     *          object should be constructed.
     */
    public function __construct($element = null, $mediaSource = null)
    {
        parent::__construct($element);
        $this->_mediaSource = $mediaSource;
    }

    /**
     * Return the MIME multipart representation of this MediaEntry.
     *
     * @return string|Zend_Gdata_MediaMimeStream The MIME multipart
     *         representation of this MediaEntry. If the entry consisted only
     *         of XML, a string is returned.
     */
    public function encode()
    {
        $xmlData = $this->saveXML();
        $mediaSource = $this->getMediaSource();
        if ($mediaSource === null) {
            // No attachment, just send XML for entry
            return $xmlData;
        } else {
            return new Zend_Gdata_MediaMimeStream($xmlData,
                $mediaSource->getFilename(), $mediaSource->getContentType());
        }
    }

    /**
     * Return the MediaSource object representing the file attached to this
     * MediaEntry.
     *
     * @return Zend_Gdata_App_MediaSource The attached MediaSource/file
     */
    public function getMediaSource()
    {
        return $this->_mediaSource;
    }

    /**
     * Set the MediaSource object (file) for this MediaEntry
     *
     * @param Zend_Gdata_App_MediaSource $value The attached MediaSource/file
     * @return Zend_Gdata_App_MediaEntry Provides a fluent interface
     */
    public function setMediaSource($value)
    {
        if ($value instanceof Zend_Gdata_App_MediaSource) {
            $this->_mediaSource = $value;
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'You must specify the media data as a class that conforms to Zend_Gdata_App_MediaSource.');
        }
        return $this;
    }

}
