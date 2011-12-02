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
 * @package    Zend_Service
 * @subpackage Flickr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Result.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Flickr
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Flickr_Result
{
    /**
     * The photo's Flickr ID.
     *
     * @var string
     */
    public $id;

    /**
     * The photo owner's NSID.
     *
     * @var string
     */
    public $owner;

    /**
     * A key used in URI construction.
     *
     * @var string
     */
    public $secret;

    /**
     * The servername to use for URI construction.
     *
     * @var string
     */
    public $server;

    /**
     * The photo's title.
     *
     * @var string
     */
    public $title;

    /**
     * Whether the photo is public.
     *
     * @var string
     */
    public $ispublic;

    /**
     * Whether the photo is visible to you because you are a friend of the owner.
     *
     * @var string
     */
    public $isfriend;

    /**
     * Whether the photo is visible to you because you are family of the owner.
     *
     * @var string
     */
    public $isfamily;

    /**
     * The license the photo is available under.
     *
     * @var string
     */
    public $license;

    /**
     * The date the photo was uploaded.
     *
     * @var string
     */
    public $dateupload;

    /**
     * The date the photo was taken.
     *
     * @var string
     */
    public $datetaken;

    /**
     * The screenname of the owner.
     *
     * @var string
     */
    public $ownername;

    /**
     * The server used in assembling icon URLs.
     *
     * @var string
     */
    public $iconserver;

    /**
     * A 75x75 pixel square thumbnail of the image.
     *
     * @var Zend_Service_Flickr_Image
     */
    public $Square;

    /**
     * A 100 pixel thumbnail of the image.
     *
     * @var Zend_Service_Flickr_Image
     */
    public $Thumbnail;

    /**
     * A 240 pixel version of the image.
     *
     * @var Zend_Service_Flickr_Image
     */
    public $Small;

    /**
     * A 500 pixel version of the image.
     *
     * @var Zend_Service_Flickr_Image
     */
    public $Medium;

    /**
     * A 640 pixel version of the image.
     *
     * @var Zend_Service_Flickr_Image
     */
    public $Large;

    /**
     * The original image.
     *
     * @var Zend_Service_Flickr_Image
     */
    public $Original;

    /**
     * Original Zend_Service_Flickr object.
     *
     * @var Zend_Service_Flickr
     */
    protected $_flickr;

    /**
     * Parse the Flickr Result
     *
     * @param  DOMElement          $image
     * @param  Zend_Service_Flickr $flickr Original Zend_Service_Flickr object with which the request was made
     * @return void
     */
    public function __construct(DOMElement $image, Zend_Service_Flickr $flickr)
    {
        $xpath = new DOMXPath($image->ownerDocument);

        foreach ($xpath->query('./@*', $image) as $property) {
            $this->{$property->name} = (string) $property->value;
        }

        $this->_flickr = $flickr;

        foreach ($this->_flickr->getImageDetails($this->id) as $k => $v) {
            $this->$k = $v;
        }
    }
}
