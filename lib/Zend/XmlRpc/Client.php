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
 * @package    Zend_XmlRpc
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Client.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * For handling the HTTP connection to the XML-RPC service
 * @see Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * Enables object chaining for calling namespaced XML-RPC methods.
 * @see Zend_XmlRpc_Client_ServerProxy
 */
#require_once 'Zend/XmlRpc/Client/ServerProxy.php';

/**
 * Introspects remote servers using the XML-RPC de facto system.* methods
 * @see Zend_XmlRpc_Client_ServerIntrospection
 */
#require_once 'Zend/XmlRpc/Client/ServerIntrospection.php';

/**
 * Represent a native XML-RPC value, used both in sending parameters
 * to methods and as the parameters retrieve from method calls
 * @see Zend_XmlRpc_Value
 */
#require_once 'Zend/XmlRpc/Value.php';

/**
 * XML-RPC Request
 * @see Zend_XmlRpc_Request
 */
#require_once 'Zend/XmlRpc/Request.php';

/**
 * XML-RPC Response
 * @see Zend_XmlRpc_Response
 */
#require_once 'Zend/XmlRpc/Response.php';

/**
 * XML-RPC Fault
 * @see Zend_XmlRpc_Fault
 */
#require_once 'Zend/XmlRpc/Fault.php';


/**
 * An XML-RPC client implementation
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Client
{
    /**
     * Full address of the XML-RPC service
     * @var string
     * @example http://time.xmlrpc.com/RPC2
     */
    protected $_serverAddress;

    /**
     * HTTP Client to use for requests
     * @var Zend_Http_Client
     */
    protected $_httpClient = null;

    /**
     * Introspection object
     * @var Zend_Http_Client_Introspector
     */
    protected $_introspector = null;

    /**
     * Request of the last method call
     * @var Zend_XmlRpc_Request
     */
    protected $_lastRequest = null;

    /**
     * Response received from the last method call
     * @var Zend_XmlRpc_Response
     */
    protected $_lastResponse = null;

    /**
     * Proxy object for more convenient method calls
     * @var array of Zend_XmlRpc_Client_ServerProxy
     */
    protected $_proxyCache = array();

    /**
     * Flag for skipping system lookup
     * @var bool
     */
    protected $_skipSystemLookup = false;

    /**
     * Create a new XML-RPC client to a remote server
     *
     * @param  string $server      Full address of the XML-RPC service
     *                             (e.g. http://time.xmlrpc.com/RPC2)
     * @param  Zend_Http_Client $httpClient HTTP Client to use for requests
     * @return void
     */
    public function __construct($server, Zend_Http_Client $httpClient = null)
    {
        if ($httpClient === null) {
            $this->_httpClient = new Zend_Http_Client();
        } else {
            $this->_httpClient = $httpClient;
        }

        $this->_introspector  = new Zend_XmlRpc_Client_ServerIntrospection($this);
        $this->_serverAddress = $server;
    }


    /**
     * Sets the HTTP client object to use for connecting the XML-RPC server.
     *
     * @param  Zend_Http_Client $httpClient
     * @return Zend_Http_Client
     */
    public function setHttpClient(Zend_Http_Client $httpClient)
    {
        return $this->_httpClient = $httpClient;
    }


    /**
     * Gets the HTTP client object.
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        return $this->_httpClient;
    }


    /**
     * Sets the object used to introspect remote servers
     *
     * @param  Zend_XmlRpc_Client_ServerIntrospection
     * @return Zend_XmlRpc_Client_ServerIntrospection
     */
    public function setIntrospector(Zend_XmlRpc_Client_ServerIntrospection $introspector)
    {
        return $this->_introspector = $introspector;
    }


    /**
     * Gets the introspection object.
     *
     * @return Zend_XmlRpc_Client_ServerIntrospection
     */
    public function getIntrospector()
    {
        return $this->_introspector;
    }


   /**
     * The request of the last method call
     *
     * @return Zend_XmlRpc_Request
     */
    public function getLastRequest()
    {
        return $this->_lastRequest;
    }


    /**
     * The response received from the last method call
     *
     * @return Zend_XmlRpc_Response
     */
    public function getLastResponse()
    {
        return $this->_lastResponse;
    }


    /**
     * Returns a proxy object for more convenient method calls
     *
     * @param $namespace  Namespace to proxy or empty string for none
     * @return Zend_XmlRpc_Client_ServerProxy
     */
    public function getProxy($namespace = '')
    {
        if (empty($this->_proxyCache[$namespace])) {
            $proxy = new Zend_XmlRpc_Client_ServerProxy($this, $namespace);
            $this->_proxyCache[$namespace] = $proxy;
        }
        return $this->_proxyCache[$namespace];
    }

    /**
     * Set skip system lookup flag
     *
     * @param  bool $flag
     * @return Zend_XmlRpc_Client
     */
    public function setSkipSystemLookup($flag = true)
    {
        $this->_skipSystemLookup = (bool) $flag;
        return $this;
    }

    /**
     * Skip system lookup when determining if parameter should be array or struct?
     *
     * @return bool
     */
    public function skipSystemLookup()
    {
        return $this->_skipSystemLookup;
    }

    /**
     * Perform an XML-RPC request and return a response.
     *
     * @param Zend_XmlRpc_Request $request
     * @param null|Zend_XmlRpc_Response $response
     * @return void
     * @throws Zend_XmlRpc_Client_HttpException
     */
    public function doRequest($request, $response = null)
    {
        $this->_lastRequest = $request;

        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $http = $this->getHttpClient();
        if($http->getUri() === null) {
            $http->setUri($this->_serverAddress);
        }

        $http->setHeaders(array(
            'Content-Type: text/xml; charset=utf-8',
            'Accept: text/xml',
        ));

        if ($http->getHeader('user-agent') === null) {
            $http->setHeaders(array('User-Agent: Zend_XmlRpc_Client'));
        }

        $xml = $this->_lastRequest->__toString();
        $http->setRawData($xml);
        $httpResponse = $http->request(Zend_Http_Client::POST);

        if (! $httpResponse->isSuccessful()) {
            /**
             * Exception thrown when an HTTP error occurs
             * @see Zend_XmlRpc_Client_HttpException
             */
            #require_once 'Zend/XmlRpc/Client/HttpException.php';
            throw new Zend_XmlRpc_Client_HttpException(
                                    $httpResponse->getMessage(),
                                    $httpResponse->getStatus());
        }

        if ($response === null) {
            $response = new Zend_XmlRpc_Response();
        }
        $this->_lastResponse = $response;
        $this->_lastResponse->loadXml($httpResponse->getBody());
    }

    /**
     * Send an XML-RPC request to the service (for a specific method)
     *
     * @param  string $method Name of the method we want to call
     * @param  array $params Array of parameters for the method
     * @return mixed
     * @throws Zend_XmlRpc_Client_FaultException
     */
    public function call($method, $params=array())
    {
        if (!$this->skipSystemLookup() && ('system.' != substr($method, 0, 7))) {
            // Ensure empty array/struct params are cast correctly
            // If system.* methods are not available, bypass. (ZF-2978)
            $success = true;
            try {
                $signatures = $this->getIntrospector()->getMethodSignature($method);
            } catch (Zend_XmlRpc_Exception $e) {
                $success = false;
            }
            if ($success) {
                $validTypes = array(
                    Zend_XmlRpc_Value::XMLRPC_TYPE_ARRAY,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_BASE64,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_BOOLEAN,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_DATETIME,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_DOUBLE,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_I4,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_INTEGER,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_NIL,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_STRING,
                    Zend_XmlRpc_Value::XMLRPC_TYPE_STRUCT,
                );

                if (!is_array($params)) {
                    $params = array($params);
                }
                foreach ($params as $key => $param) {

                    if ($param instanceof Zend_XmlRpc_Value) {
                        continue;
                    }

                    $type = Zend_XmlRpc_Value::AUTO_DETECT_TYPE;
                    foreach ($signatures as $signature) {
                        if (!is_array($signature)) {
                            continue;
                        }

                        if (isset($signature['parameters'][$key])) {
                            $type = $signature['parameters'][$key];
                            $type = in_array($type, $validTypes) ? $type : Zend_XmlRpc_Value::AUTO_DETECT_TYPE;
                        }
                    }

                    $params[$key] = Zend_XmlRpc_Value::getXmlRpcValue($param, $type);
                }
            }
        }

        $request = $this->_createRequest($method, $params);

        $this->doRequest($request);

        if ($this->_lastResponse->isFault()) {
            $fault = $this->_lastResponse->getFault();
            /**
             * Exception thrown when an XML-RPC fault is returned
             * @see Zend_XmlRpc_Client_FaultException
             */
            #require_once 'Zend/XmlRpc/Client/FaultException.php';
            throw new Zend_XmlRpc_Client_FaultException($fault->getMessage(),
                                                        $fault->getCode());
        }

        return $this->_lastResponse->getReturnValue();
    }

    /**
     * Create request object
     *
     * @return Zend_XmlRpc_Request
     */
    protected function _createRequest($method, $params)
    {
        return new Zend_XmlRpc_Request($method, $params);
    }
}
