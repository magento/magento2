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
 * @version    $Id: SearchResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Result
 */
#require_once 'Zend/Service/Technorati/Result.php';


/**
 * Represents a single Technorati Search query result object.
 * It is never returned as a standalone object,
 * but it always belongs to a valid Zend_Service_Technorati_SearchResultSet object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_SearchResult extends Zend_Service_Technorati_Result
{
    /**
     * Technorati weblog object corresponding to queried keyword.
     *
     * @var     Zend_Service_Technorati_Weblog
     * @access  protected
     */
    protected $_weblog;

    /**
     * The title of the entry.
     *
     * @var     string
     * @access  protected
     */
    protected $_title;

    /**
     * The blurb from entry with search term highlighted.
     *
     * @var     string
     * @access  protected
     */
    protected $_excerpt;

    /**
     * The datetime the entry was created.
     *
     * @var     Zend_Date
     * @access  protected
     */
    protected $_created;

    /**
     * The permalink of the blog entry.
     *
     * @var     Zend_Uri_Http
     * @access  protected
     */
    protected $_permalink;


    /**
     * Constructs a new object object from DOM Element.
     *
     * @param   DomElement $dom the ReST fragment for this object
     */
    public function __construct(DomElement $dom)
    {
        $this->_fields = array( '_permalink'    => 'permalink',
                                '_excerpt'      => 'excerpt',
                                '_created'      => 'created',
                                '_title'        => 'title');
        parent::__construct($dom);

        // weblog object field
        $this->_parseWeblog();

        // filter fields
        $this->_permalink = Zend_Service_Technorati_Utils::normalizeUriHttp($this->_permalink);
        $this->_created = Zend_Service_Technorati_Utils::normalizeDate($this->_created);
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
     * Returns the title of the entry.
     *
     * @return  string
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Returns the blurb from entry with search term highlighted.
     *
     * @return  string
     */
    public function getExcerpt() {
        return $this->_excerpt;
    }

    /**
     * Returns the datetime the entry was created.
     *
     * @return  Zend_Date
     */
    public function getCreated() {
        return $this->_created;
    }

    /**
     * Returns the permalink of the blog entry.
     *
     * @return  Zend_Uri_Http
     */
    public function getPermalink() {
        return $this->_permalink;
    }

}
