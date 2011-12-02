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
 * @version    $Id: KeyInfoResult.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Represents a single Technorati KeyInfo query result object.
 * It provides information about your Technorati API Key daily usage.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati_KeyInfoResult
{
    /**
     * Technorati API key
     *
     * @var     string
     * @access  protected
     */
    protected $_apiKey;

    /**
     * Number of queries used today
     *
     * @var     int
     * @access  protected
     */
    protected $_apiQueries;

    /**
     * Total number of available queries per day
     *
     * @var     int
     * @access  protected
     */
    protected $_maxQueries;


    /**
     * Constructs a new object from DOM Element.
     * Parses given Key element from $dom and sets API key string.
     *
     * @param   DomElement $dom the ReST fragment for this object
     * @param   string $apiKey  the API Key string
     */
    public function __construct(DomDocument $dom, $apiKey = null)
    {
        // $this->_dom   = $dom;
        // $this->_xpath = new DOMXPath($dom);
        $xpath = new DOMXPath($dom);

        $this->_apiQueries   = (int) $xpath->query('/tapi/document/result/apiqueries/text()')->item(0)->data;
        $this->_maxQueries   = (int) $xpath->query('/tapi/document/result/maxqueries/text()')->item(0)->data;
        $this->setApiKey($apiKey);
    }


    /**
     * Returns API Key string.
     *
     * @return  string  API Key string
     */
    public function getApiKey() {
        return $this->_apiKey;
    }

    /**
     * Returns the number of queries sent today.
     *
     * @return  int     number of queries sent today
     */
    public function getApiQueries() {
        return $this->_apiQueries;
    }

    /**
     * Returns Key's daily query limit.
     *
     * @return  int     maximum number of available queries per day
     */
    public function getMaxQueries() {
        return $this->_maxQueries;
    }


    /**
     * Sets API Key string.
     *
     * @param   string $apiKey  the API Key
     * @return  Zend_Service_Technorati_KeyInfoResult $this instance
     */
    public function setApiKey($apiKey) {
        $this->_apiKey = $apiKey;
        return $this;
    }
}
