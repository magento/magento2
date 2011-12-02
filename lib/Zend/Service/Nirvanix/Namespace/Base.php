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
 * @subpackage Nirvanix
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Base.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Service_Nirvanix_Response
 */
#require_once 'Zend/Service/Nirvanix/Response.php';

/**
 * The Nirvanix web services are split into namespaces.  This is a proxy class
 * representing one namespace.  It allows calls to the namespace to be made by
 * PHP object calls rather than by having to construct HTTP client requests.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Nirvanix
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Nirvanix_Namespace_Base
{
    /**
     * HTTP client instance that will be used to make calls to
     * the Nirvanix web services.
     * @var Zend_Http_Client
     */
    protected $_httpClient;

    /**
     * Host to use for calls to this Nirvanix namespace.  It is possible
     * that the user will wish to use different hosts for different namespaces.
     * @var string
     */
    protected $_host = 'http://services.nirvanix.com';

    /**
     * Name of this namespace as used in the URL.
     * @var string
     */
    protected $_namespace = '';

    /**
     * Defaults for POST parameters.  When a request to the service is to be
     * made, the POST parameters are merged into these.  This is a convenience
     * feature so parameters that are repeatedly required like sessionToken
     * do not need to be supplied again and again by the user.
     *
     * @param array
     */
    protected $_defaults = array();

    /**
     * Class constructor.
     *
     * @param  $options  array  Options and dependency injection
     */
    public function __construct($options = array())
    {
        if (isset($options['baseUrl'])) {
            $this->_host = $options['baseUrl'];
        }

        if (isset($options['namespace'])) {
            $this->_namespace = $options['namespace'];
        }

        if (isset($options['defaults'])) {
            $this->_defaults = $options['defaults'];
        }

        if (! isset($options['httpClient'])) {
            $options['httpClient'] = new Zend_Http_Client();
        }
        $this->_httpClient = $options['httpClient'];
    }

    /**
     * When a method call is made against this proxy, convert it to
     * an HTTP request to make against the Nirvanix REST service.
     *
     * $imfs->DeleteFiles(array('filePath' => 'foo'));
     *
     * Assuming this object was proxying the IMFS namespace, the
     * method call above would call the DeleteFiles command.  The
     * POST parameters would be filePath, merged with the
     * $this->_defaults (containing the sessionToken).
     *
     * @param  string  $methodName  Name of the command to call
     *                              on this namespace.
     * @param  array   $args        Only the first is used and it must be
     *                              an array.  It contains the POST params.
     *
     * @return Zend_Service_Nirvanix_Response
     */
    public function __call($methodName, $args)
    {
        $uri = $this->_makeUri($methodName);
        $this->_httpClient->setUri($uri);

        if (!isset($args[0]) || !is_array($args[0])) {
            $args[0] = array();
        }

        $params = array_merge($this->_defaults, $args[0]);
        $this->_httpClient->resetParameters();
        $this->_httpClient->setParameterPost($params);

        $httpResponse = $this->_httpClient->request(Zend_Http_Client::POST);
        return $this->_wrapResponse($httpResponse);
    }

    /**
     * Return the HTTP client used for this namespace.  This is useful
     * for inspecting the last request or directly interacting with the
     * HTTP client.
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        return $this->_httpClient;
    }

    /**
     * Make a complete URI from an RPC method name.  All Nirvanix REST
     * service URIs use the same format.
     *
     * @param  string  $methodName  RPC method name
     * @return string
     */
    protected function _makeUri($methodName)
    {
        $methodName = ucfirst($methodName);
        return "{$this->_host}/ws/{$this->_namespace}/{$methodName}.ashx";
    }

    /**
     * All Nirvanix REST service calls return an XML payload.  This method
     * makes a Zend_Service_Nirvanix_Response from that XML payload.
     *
     * @param  Zend_Http_Response  $httpResponse  Raw response from Nirvanix
     * @return Zend_Service_Nirvanix_Response     Wrapped response
     */
    protected function _wrapResponse($httpResponse)
    {
        return new Zend_Service_Nirvanix_Response($httpResponse->getBody());
    }
}
