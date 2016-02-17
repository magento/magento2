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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Zend_Gdata_App_Exception
 */
#require_once 'Zend/Gdata/App/Exception.php';

/**
 * Zend_Http_Client_Exception
 */
#require_once 'Zend/Http/Client/Exception.php';

/**
 * Gdata exceptions
 *
 * Class to represent exceptions that occur during Gdata operations.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage App
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_App_HttpException extends Zend_Gdata_App_Exception
{

    protected $_httpClientException = null;
    protected $_response = null;

    /**
     * Create a new Zend_Gdata_App_HttpException
     *
     * @param  string $message Optionally set a message
     * @param Zend_Http_Client_Exception Optionally pass in a Zend_Http_Client_Exception
     * @param Zend_Http_Response Optionally pass in a Zend_Http_Response
     */
    public function __construct($message = null, $e = null, $response = null)
    {
        $this->_httpClientException = $e;
        $this->_response = $response;
        parent::__construct($message);
    }

    /**
     * Get the Zend_Http_Client_Exception.
     *
     * @return Zend_Http_Client_Exception
     */
    public function getHttpClientException()
    {
        return $this->_httpClientException;
    }

    /**
     * Set the Zend_Http_Client_Exception.
     *
     * @param Zend_Http_Client_Exception $value
     */
    public function setHttpClientException($value)
    {
        $this->_httpClientException = $value;
        return $this;
    }

    /**
     * Set the Zend_Http_Response.
     *
     * @param Zend_Http_Response $response
     */
    public function setResponse($response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Get the Zend_Http_Response.
     *
     * @return Zend_Http_Response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Get the body of the Zend_Http_Response
     *
     * @return string
     */
    public function getRawResponseBody()
    {
        if ($this->getResponse()) {
            $response = $this->getResponse();
            return $response->getRawBody();
        }
        return null;
    }

}
