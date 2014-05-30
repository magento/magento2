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
 * @version    $Id: BlogInfoResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * @see Zend_Service_Technorati_Utils
 */
#require_once 'Zend/Service/Technorati/Utils.php';


/**
 * Represents a single Technorati BlogInfo query result object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_BlogInfoResult
{
    /**
     * Technorati weblog url, if queried URL is a valid weblog.
     *
     * @var     Zend_Uri_Http
     * @access  protected
     */
    protected $_url;

    /**
     * Technorati weblog, if queried URL is a valid weblog.
     *
     * @var     Zend_Service_Technorati_Weblog
     * @access  protected
     */
    protected $_weblog;

    /**
     * Number of unique blogs linking this blog
     *
     * @var     integer
     * @access  protected
     */
    protected $_inboundBlogs;

    /**
     * Number of incoming links to this blog
     *
     * @var     integer
     * @access  protected
     */
    protected $_inboundLinks;


    /**
     * Constructs a new object object from DOM Document.
     *
     * @param   DomDocument $dom the ReST fragment for this object
     */
    public function __construct(DomDocument $dom)
    {
        $xpath = new DOMXPath($dom);
        /**
         * @see Zend_Service_Technorati_Weblog
         */
        #require_once 'Zend/Service/Technorati/Weblog.php';

        $result = $xpath->query('//result/weblog');
        if ($result->length == 1) {
            $this->_weblog = new Zend_Service_Technorati_Weblog($result->item(0));
        } else {
            // follow the same behavior of blogPostTags
            // and raise an Exception if the URL is not a valid weblog
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                "Your URL is not a recognized Technorati weblog");
        }

        $result = $xpath->query('//result/url/text()');
        if ($result->length == 1) {
            try {
                // fetched URL often doens't include schema
                // and this issue causes the following line to fail
                $this->_url = Zend_Service_Technorati_Utils::normalizeUriHttp($result->item(0)->data);
            } catch(Zend_Service_Technorati_Exception $e) {
                if ($this->getWeblog() instanceof Zend_Service_Technorati_Weblog) {
                    $this->_url = $this->getWeblog()->getUrl();
                }
            }
        }

        $result = $xpath->query('//result/inboundblogs/text()');
        if ($result->length == 1) $this->_inboundBlogs = (int) $result->item(0)->data;

        $result = $xpath->query('//result/inboundlinks/text()');
        if ($result->length == 1) $this->_inboundLinks = (int) $result->item(0)->data;

    }


    /**
     * Returns the weblog URL.
     *
     * @return  Zend_Uri_Http
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * Returns the weblog.
     *
     * @return  Zend_Service_Technorati_Weblog
     */
    public function getWeblog() {
        return $this->_weblog;
    }

    /**
     * Returns number of unique blogs linking this blog.
     *
     * @return  integer the number of inbound blogs
     */
    public function getInboundBlogs()
    {
        return (int) $this->_inboundBlogs;
    }

    /**
     * Returns number of incoming links to this blog.
     *
     * @return  integer the number of inbound links
     */
    public function getInboundLinks()
    {
        return (int) $this->_inboundLinks;
    }

}
