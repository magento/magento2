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
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Search.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Http_Client
 */
#require_once 'Zend/Rest/Client.php';

/**
 * @see Zend_Json
 */
#require_once 'Zend/Json.php';

/**
 * @see Zend_Feed
 */
#require_once 'Zend/Feed.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Service_Twitter_Search extends Zend_Rest_Client
{
    /**
     * Return Type
     * @var String
     */
    protected $_responseType = 'json';

    /**
     * Response Format Types
     * @var array
     */
    protected $_responseTypes = array(
        'atom',
        'json'
    );

    /**
     * Uri Compoent
     *
     * @var Zend_Uri_Http
     */
    protected $_uri;

    /**
     * Constructor
     *
     * @param  string $returnType
     * @return void
     */
    public function __construct($responseType = 'json')
    {
        $this->setResponseType($responseType);
        $this->setUri("http://search.twitter.com");

        $this->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
    }

    /**
     * set responseType
     *
     * @param string $responseType
     * @throws Zend_Service_Twitter_Exception
     * @return Zend_Service_Twitter_Search
     */
    public function setResponseType($responseType = 'json')
    {
        if(!in_array($responseType, $this->_responseTypes, TRUE)) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception('Invalid Response Type');
        }
        $this->_responseType = $responseType;
        return $this;
    }

    /**
     * Retrieve responseType
     *
     * @return string
     */
    public function getResponseType()
    {
        return $this->_responseType;
    }

    /**
     * Get the current twitter trends.  Currnetly only supports json as the return.
     *
     * @throws Zend_Http_Client_Exception
     * @return array
     */
    public function trends()
    {
        $response     = $this->restGet('/trends.json');

        return Zend_Json::decode($response->getBody());
    }

    /**
     * Performs a Twitter search query.
     *
     * @throws Zend_Http_Client_Exception
     */
    public function search($query, array $params = array())
    {

        $_query = array();

        $_query['q'] = $query;

        foreach($params as $key=>$param) {
            switch($key) {
                case 'geocode':
                case 'lang':
                case 'since_id':
                    $_query[$key] = $param;
                    break;
                case 'rpp':
                    $_query[$key] = (intval($param) > 100) ? 100 : intval($param);
                    break;
                case 'page':
                    $_query[$key] = intval($param);
                    break;
                case 'show_user':
                    $_query[$key] = 'true';
            }
        }

        $response = $this->restGet('/search.' . $this->_responseType, $_query);

        switch($this->_responseType) {
            case 'json':
                return Zend_Json::decode($response->getBody());
                break;
            case 'atom':
                return Zend_Feed::importString($response->getBody());
                break;
        }

        return ;
    }
}
