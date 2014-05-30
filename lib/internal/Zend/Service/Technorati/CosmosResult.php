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
 * @version    $Id: CosmosResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Result
 */
#require_once 'Zend/Service/Technorati/Result.php';


/**
 * Represents a single Technorati Cosmos query result object.
 * It is never returned as a standalone object,
 * but it always belongs to a valid Zend_Service_Technorati_CosmosResultSet object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_CosmosResult extends Zend_Service_Technorati_Result
{
    /**
     * Technorati weblog object that links queried URL.
     *
     * @var     Zend_Service_Technorati_Weblog
     * @access  protected
     */
    protected $_weblog;

    /**
     * The nearest permalink tracked for queried URL.
     *
     * @var     Zend_Uri_Http
     * @access  protected
     */
    protected $_nearestPermalink;

    /**
     * The excerpt of the blog/page linking queried URL.
     *
     * @var     string
     * @access  protected
     */
    protected $_excerpt;

    /**
     * The the datetime the link was created.
     *
     * @var     Zend_Date
     * @access  protected
     */
    protected $_linkCreated;

    /**
     * The URL of the specific link target page
     *
     * @var     Zend_Uri_Http
     * @access  protected
     */
    protected $_linkUrl;


    /**
     * Constructs a new object object from DOM Element.
     *
     * @param   DomElement $dom the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $this->_fields = array( '_nearestPermalink' => 'nearestpermalink',
                                '_excerpt'          => 'excerpt',
                                '_linkCreated'      => 'linkcreated',
                                '_linkUrl'          => 'linkurl');
        parent::__construct($dom);

        // weblog object field
        $this->_parseWeblog();

        // filter fields
        $this->_nearestPermalink = Zend_Service_Technorati_Utils::normalizeUriHttp($this->_nearestPermalink);
        $this->_linkUrl = Zend_Service_Technorati_Utils::normalizeUriHttp($this->_linkUrl);
        $this->_linkCreated = Zend_Service_Technorati_Utils::normalizeDate($this->_linkCreated);
    }

    /**
     * Returns the weblog object that links queried URL.
     *
     * @return  Zend_Service_Technorati_Weblog
     */
    public function getWeblog() {
        return $this->_weblog;
    }

    /**
     * Returns the nearest permalink tracked for queried URL.
     *
     * @return  Zend_Uri_Http
     */
    public function getNearestPermalink() {
        return $this->_nearestPermalink;
    }

    /**
     * Returns the excerpt of the blog/page linking queried URL.
     *
     * @return  string
     */
    public function getExcerpt() {
        return $this->_excerpt;
    }

    /**
     * Returns the datetime the link was created.
     *
     * @return  Zend_Date
     */
    public function getLinkCreated() {
        return $this->_linkCreated;
    }

    /**
     * If queried URL is a valid blog,
     * returns the URL of the specific link target page.
     *
     * @return  Zend_Uri_Http
     */
    public function getLinkUrl() {
        return $this->_linkUrl;
    }

}
