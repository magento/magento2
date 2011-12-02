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
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Weblog.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Author
 */
#require_once 'Zend/Service/Technorati/Author.php';

/**
 * @see Zend_Service_Technorati_Utils
 */
#require_once 'Zend/Service/Technorati/Utils.php';


/**
 * Represents a Weblog object successful recognized by Technorati.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_Weblog
{
    /**
     * Blog name as written in the feed.
     *
     * @var     string
     * @access  protected
     */
    protected $_name;

    /**
     * Base blog URL.
     *
     * @var     Zend_Uri_Http
     * @access  protected
     */
    protected $_url;

    /**
     * RSS feed URL, if any.
     *
     * @var     null|Zend_Uri_Http
     * @access  protected
     */
    protected $_rssUrl;

    /**
     * Atom feed URL, if any.
     *
     * @var     null|Zend_Uri_Http
     * @access  protected
     */
    protected $_atomUrl;

    /**
     * Number of unique blogs linking this blog.
     *
     * @var     integer
     * @access  protected
     */
    protected $_inboundBlogs;

    /**
     * Number of incoming links to this blog.
     *
     * @var     integer
     * @access  protected
     */
    protected $_inboundLinks;

    /**
     * Last blog update UNIX timestamp.
     *
     * @var     null|Zend_Date
     * @access  protected
     */
    protected $_lastUpdate;

    /**
     * Technorati rank value for this weblog.
     *
     * Note. This property has no official documentation.
     *
     * @var     integer
     * @access  protected
     */
    protected $_rank;

    /**
     * Blog latitude coordinate.
     *
     * Note. This property has no official documentation.
     *
     * @var     float
     * @access  protected
     */
    protected $_lat;

    /**
     * Blog longitude coordinate.
     *
     * Note. This property has no official documentation.
     *
     * @var     float
     * @access  protected
     */
    protected $_lon;

    /**
     * Whether the author who claimed this weblog has a photo.
     *
     * Note. This property has no official documentation.
     *
     * @var     bool
     * @access  protected
     * @see     Zend_Service_Technorati_Author::$thumbnailPicture
     */
    protected $_hasPhoto = false;

    /**
     * An array of Zend_Service_Technorati_Author who claimed this blog
     *
     * @var     array
     * @access  protected
     */
    protected $_authors = array();


    /**
     * Constructs a new object from DOM Element.
     *
     * @param  DomElement $dom the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $xpath = new DOMXPath($dom->ownerDocument);

        $result = $xpath->query('./name/text()', $dom);
        if ($result->length == 1) $this->setName($result->item(0)->data);

        $result = $xpath->query('./url/text()', $dom);
        if ($result->length == 1) $this->setUrl($result->item(0)->data);

        $result = $xpath->query('./inboundblogs/text()', $dom);
        if ($result->length == 1) $this->setInboundBlogs($result->item(0)->data);

        $result = $xpath->query('./inboundlinks/text()', $dom);
        if ($result->length == 1) $this->setInboundLinks($result->item(0)->data);

        $result = $xpath->query('./lastupdate/text()', $dom);
        if ($result->length == 1) $this->setLastUpdate($result->item(0)->data);

        /* The following elements need more attention */

        $result = $xpath->query('./rssurl/text()', $dom);
        if ($result->length == 1) $this->setRssUrl($result->item(0)->data);

        $result = $xpath->query('./atomurl/text()', $dom);
        if ($result->length == 1) $this->setAtomUrl($result->item(0)->data);

        $result = $xpath->query('./author', $dom);
        if ($result->length >= 1) {
            foreach ($result as $author) {
                $this->_authors[] = new Zend_Service_Technorati_Author($author);
            }
        }

        /**
         * The following are optional elements
         *
         * I can't find any official documentation about the following properties
         * however they are included in response DTD and/or test responses.
         */

        $result = $xpath->query('./rank/text()', $dom);
        if ($result->length == 1) $this->setRank($result->item(0)->data);

        $result = $xpath->query('./lat/text()', $dom);
        if ($result->length == 1) $this->setLat($result->item(0)->data);

        $result = $xpath->query('./lon/text()', $dom);
        if ($result->length == 1) $this->setLon($result->item(0)->data);

        $result = $xpath->query('./hasphoto/text()', $dom);
        if ($result->length == 1) $this->setHasPhoto($result->item(0)->data);
    }


    /**
     * Returns weblog name.
     *
     * @return  string  Weblog name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns weblog URL.
     *
     * @return  null|Zend_Uri_Http object representing weblog base URL
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Returns number of unique blogs linking this blog.
     *
     * @return  integer the number of inbound blogs
     */
    public function getInboundBlogs()
    {
        return $this->_inboundBlogs;
    }

    /**
     * Returns number of incoming links to this blog.
     *
     * @return  integer the number of inbound links
     */
    public function getInboundLinks()
    {
        return $this->_inboundLinks;
    }

    /**
     * Returns weblog Rss URL.
     *
     * @return  null|Zend_Uri_Http object representing the URL
     *          of the RSS feed for given blog
     */
    public function getRssUrl()
    {
        return $this->_rssUrl;
    }

    /**
     * Returns weblog Atom URL.
     *
     * @return  null|Zend_Uri_Http object representing the URL
     *          of the Atom feed for given blog
     */
    public function getAtomUrl()
    {
        return $this->_atomUrl;
    }

    /**
     * Returns UNIX timestamp of the last weblog update.
     *
     * @return  integer UNIX timestamp of the last weblog update
     */
    public function getLastUpdate()
    {
        return $this->_lastUpdate;
    }

    /**
     * Returns weblog rank value.
     *
     * Note. This property is not documented.
     *
     * @return  integer weblog rank value
     */
    public function getRank()
    {
        return $this->_rank;
    }

    /**
     * Returns weblog latitude coordinate.
     *
     * Note. This property is not documented.
     *
     * @return  float   weblog latitude coordinate
     */
    public function getLat() {
        return $this->_lat;
    }

    /**
     * Returns weblog longitude coordinate.
     *
     * Note. This property is not documented.
     *
     * @return  float   weblog longitude coordinate
     */
    public function getLon()
    {
        return $this->_lon;
    }

    /**
     * Returns whether the author who claimed this weblog has a photo.
     *
     * Note. This property is not documented.
     *
     * @return  bool    TRUE if the author who claimed this weblog has a photo,
     *                  FALSE otherwise.
     */
    public function hasPhoto()
    {
        return (bool) $this->_hasPhoto;
    }

    /**
     * Returns the array of weblog authors.
     *
     * @return  array of Zend_Service_Technorati_Author authors
     */
    public function getAuthors()
    {
        return (array) $this->_authors;
    }


    /**
     * Sets weblog name.
     *
     * @param   string $name
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setName($name)
    {
        $this->_name = (string) $name;
        return $this;
    }

    /**
     * Sets weblog URL.
     *
     * @param   string|Zend_Uri_Http $url
     * @return  void
     * @throws  Zend_Service_Technorati_Exception if $input is an invalid URI
     *          (via Zend_Service_Technorati_Utils::normalizeUriHttp)
     */
    public function setUrl($url)
    {
        $this->_url = Zend_Service_Technorati_Utils::normalizeUriHttp($url);
        return $this;
    }

    /**
     * Sets number of inbound blogs.
     *
     * @param   integer $number
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setInboundBlogs($number)
    {
        $this->_inboundBlogs = (int) $number;
        return $this;
    }

    /**
     * Sets number of Iinbound links.
     *
     * @param   integer $number
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setInboundLinks($number)
    {
        $this->_inboundLinks = (int) $number;
        return $this;
    }

    /**
     * Sets weblog Rss URL.
     *
     * @param   string|Zend_Uri_Http $url
     * @return  Zend_Service_Technorati_Weblog $this instance
     * @throws  Zend_Service_Technorati_Exception if $input is an invalid URI
     *          (via Zend_Service_Technorati_Utils::normalizeUriHttp)
     */
    public function setRssUrl($url)
    {
        $this->_rssUrl = Zend_Service_Technorati_Utils::normalizeUriHttp($url);
        return $this;
    }

    /**
     * Sets weblog Atom URL.
     *
     * @param   string|Zend_Uri_Http $url
     * @return  Zend_Service_Technorati_Weblog $this instance
     * @throws  Zend_Service_Technorati_Exception if $input is an invalid URI
     *          (via Zend_Service_Technorati_Utils::normalizeUriHttp)
     */
    public function setAtomUrl($url)
    {
        $this->_atomUrl = Zend_Service_Technorati_Utils::normalizeUriHttp($url);
        return $this;
    }

    /**
     * Sets weblog Last Update timestamp.
     *
     * $datetime can be any value supported by
     * Zend_Service_Technorati_Utils::normalizeDate().
     *
     * @param   mixed $datetime A string representing the last update date time
     *                          in a valid date time format
     * @return  Zend_Service_Technorati_Weblog $this instance
     * @throws  Zend_Service_Technorati_Exception
     */
    public function setLastUpdate($datetime)
    {
        $this->_lastUpdate = Zend_Service_Technorati_Utils::normalizeDate($datetime);
        return $this;
    }

    /**
     * Sets weblog Rank.
     *
     * @param   integer $rank
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setRank($rank)
    {
        $this->_rank = (int) $rank;
        return $this;
    }

    /**
     * Sets weblog latitude coordinate.
     *
     * @param   float $coordinate
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setLat($coordinate)
    {
        $this->_lat = (float) $coordinate;
        return $this;
    }

    /**
     * Sets weblog longitude coordinate.
     *
     * @param   float $coordinate
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setLon($coordinate)
    {
        $this->_lon = (float) $coordinate;
        return $this;
    }

    /**
     * Sets hasPhoto property.
     *
     * @param   bool $hasPhoto
     * @return  Zend_Service_Technorati_Weblog $this instance
     */
    public function setHasPhoto($hasPhoto)
    {
        $this->_hasPhoto = (bool) $hasPhoto;
        return $this;
    }

}
