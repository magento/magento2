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
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Server.php 22237 2010-05-21 23:58:00Z andyfowler $
 */

/**
 * @see Zend_Server_Abstract
 */
#require_once 'Zend/Server/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server extends Zend_Server_Abstract
{
    /**#@+
     * Version Constants
     */
    const VERSION_1 = '1.0';
    const VERSION_2 = '2.0';
    /**#@-*/

    /**
     * Flag: whether or not to auto-emit the response
     * @var bool
     */
    protected $_autoEmitResponse = true;

    /**
     * @var bool Flag; allow overwriting existing methods when creating server definition
     */
    protected $_overwriteExistingMethods = true;

    /**
     * Request object
     * @var Zend_Json_Server_Request
     */
    protected $_request;

    /**
     * Response object
     * @var Zend_Json_Server_Response
     */
    protected $_response;

    /**
     * SMD object
     * @var Zend_Json_Server_Smd
     */
    protected $_serviceMap;

    /**
     * SMD class accessors
     * @var array
     */
    protected $_smdMethods;

    /**
     * @var Zend_Server_Description
     */
    protected $_table;

    /**
     * Attach a function or callback to the server
     *
     * @param  string|array $function Valid PHP callback
     * @param  string $namespace  Ignored
     * @return Zend_Json_Server
     */
    public function addFunction($function, $namespace = '')
    {
        if (!is_string($function) && (!is_array($function) || (2 > count($function)))) {
            #require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Unable to attach function; invalid');
        }

        if (!is_callable($function)) {
            #require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Unable to attach function; does not exist');
        }

        $argv = null;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 2);
        }

        #require_once 'Zend/Server/Reflection.php';
        if (is_string($function)) {
            $method = Zend_Server_Reflection::reflectFunction($function, $argv, $namespace);
        } else {
            $class  = array_shift($function);
            $action = array_shift($function);
            $reflection = Zend_Server_Reflection::reflectClass($class, $argv, $namespace);
            $methods = $reflection->getMethods();
            $found   = false;
            foreach ($methods as $method) {
                if ($action == $method->getName()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->fault('Method not found', -32601);
                return $this;
            }
        }

        $definition = $this->_buildSignature($method);
        $this->_addMethodServiceMap($definition);

        return $this;
    }

    /**
     * Register a class with the server
     *
     * @param  string $class
     * @param  string $namespace Ignored
     * @param  mixed $argv Ignored
     * @return Zend_Json_Server
     */
    public function setClass($class, $namespace = '', $argv = null)
    {
        $argv = null;
        if (3 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 3);
        }

        #require_once 'Zend/Server/Reflection.php';
        $reflection = Zend_Server_Reflection::reflectClass($class, $argv, $namespace);

        foreach ($reflection->getMethods() as $method) {
            $definition = $this->_buildSignature($method, $class);
            $this->_addMethodServiceMap($definition);
        }
        return $this;
    }

    /**
     * Indicate fault response
     *
     * @param  string $fault
     * @param  int $code
     * @return false
     */
    public function fault($fault = null, $code = 404, $data = null)
    {
        #require_once 'Zend/Json/Server/Error.php';
        $error = new Zend_Json_Server_Error($fault, $code, $data);
        $this->getResponse()->setError($error);
        return $error;
    }

    /**
     * Handle request
     *
     * @param  Zend_Json_Server_Request $request
     * @return null|Zend_Json_Server_Response
     */
    public function handle($request = false)
    {
        if ((false !== $request) && (!$request instanceof Zend_Json_Server_Request)) {
            #require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Invalid request type provided; cannot handle');
        } elseif ($request) {
            $this->setRequest($request);
        }

        // Handle request
        $this->_handle();

        // Get response
        $response = $this->_getReadyResponse();

        // Emit response?
        if ($this->autoEmitResponse()) {
            echo $response;
            return;
        }

        // or return it?
        return $response;
    }

    /**
     * Load function definitions
     *
     * @param  array|Zend_Server_Definition $definition
     * @return void
     */
    public function loadFunctions($definition)
    {
        if (!is_array($definition) && (!$definition instanceof Zend_Server_Definition)) {
            #require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Invalid definition provided to loadFunctions()');
        }

        foreach ($definition as $key => $method) {
            $this->_table->addMethod($method, $key);
            $this->_addMethodServiceMap($method);
        }
    }

    public function setPersistence($mode)
    {
    }

    /**
     * Set request object
     *
     * @param  Zend_Json_Server_Request $request
     * @return Zend_Json_Server
     */
    public function setRequest(Zend_Json_Server_Request $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * Get JSON-RPC request object
     *
     * @return Zend_Json_Server_Request
     */
    public function getRequest()
    {
        if (null === ($request = $this->_request)) {
            #require_once 'Zend/Json/Server/Request/Http.php';
            $this->setRequest(new Zend_Json_Server_Request_Http());
        }
        return $this->_request;
    }

    /**
     * Set response object
     *
     * @param  Zend_Json_Server_Response $response
     * @return Zend_Json_Server
     */
    public function setResponse(Zend_Json_Server_Response $response)
    {
        $this->_response = $response;
        return $this;
    }

    /**
     * Get response object
     *
     * @return Zend_Json_Server_Response
     */
    public function getResponse()
    {
        if (null === ($response = $this->_response)) {
            #require_once 'Zend/Json/Server/Response/Http.php';
            $this->setResponse(new Zend_Json_Server_Response_Http());
        }
        return $this->_response;
    }

    /**
     * Set flag indicating whether or not to auto-emit response
     *
     * @param  bool $flag
     * @return Zend_Json_Server
     */
    public function setAutoEmitResponse($flag)
    {
        $this->_autoEmitResponse = (bool) $flag;
        return $this;
    }

    /**
     * Will we auto-emit the response?
     *
     * @return bool
     */
    public function autoEmitResponse()
    {
        return $this->_autoEmitResponse;
    }

    // overloading for SMD metadata
    /**
     * Overload to accessors of SMD object
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (preg_match('/^(set|get)/', $method, $matches)) {
            if (in_array($method, $this->_getSmdMethods())) {
                if ('set' == $matches[1]) {
                    $value = array_shift($args);
                    $this->getServiceMap()->$method($value);
                    return $this;
                } else {
                    return $this->getServiceMap()->$method();
                }
            }
        }
        return null;
    }

    /**
     * Retrieve SMD object
     *
     * @return Zend_Json_Server_Smd
     */
    public function getServiceMap()
    {
        if (null === $this->_serviceMap) {
            #require_once 'Zend/Json/Server/Smd.php';
            $this->_serviceMap = new Zend_Json_Server_Smd();
        }
        return $this->_serviceMap;
    }

    /**
     * Add service method to service map
     *
     * @param  Zend_Server_Reflection_Function $method
     * @return void
     */
    protected function _addMethodServiceMap(Zend_Server_Method_Definition $method)
    {
        $serviceInfo = array(
            'name'   => $method->getName(),
            'return' => $this->_getReturnType($method),
        );
        $params = $this->_getParams($method);
        $serviceInfo['params'] = $params;
        $serviceMap = $this->getServiceMap();
        if (false !== $serviceMap->getService($serviceInfo['name'])) {
            $serviceMap->removeService($serviceInfo['name']);
        }
        $serviceMap->addService($serviceInfo);
    }

    /**
     * Translate PHP type to JSON type
     *
     * @param  string $type
     * @return string
     */
    protected function _fixType($type)
    {
        return $type;
    }

    /**
     * Get default params from signature
     *
     * @param  array $args
     * @param  array $params
     * @return array
     */
    protected function _getDefaultParams(array $args, array $params)
    {
        $defaultParams = array_slice($params, count($args));
        foreach ($defaultParams as $param) {
            $value = null;
            if (array_key_exists('default', $param)) {
                $value = $param['default'];
            }
            array_push($args, $value);
        }
        return $args;
    }

    /**
     * Get method param type
     *
     * @param  Zend_Server_Reflection_Function_Abstract $method
     * @return string|array
     */
    protected function _getParams(Zend_Server_Method_Definition $method)
    {
        $params = array();
        foreach ($method->getPrototypes() as $prototype) {
            foreach ($prototype->getParameterObjects() as $key => $parameter) {
                if (!isset($params[$key])) {
                    $params[$key] = array(
                        'type'     => $parameter->getType(),
                        'name'     => $parameter->getName(),
                        'optional' => $parameter->isOptional(),
                    );
                    if (null !== ($default = $parameter->getDefaultValue())) {
                        $params[$key]['default'] = $default;
                    }
                    $description = $parameter->getDescription();
                    if (!empty($description)) {
                        $params[$key]['description'] = $description;
                    }
                    continue;
                }
                $newType = $parameter->getType();
                if (!is_array($params[$key]['type'])) {
                    if ($params[$key]['type'] == $newType) {
                        continue;
                    }
                    $params[$key]['type'] = (array) $params[$key]['type'];
                } elseif (in_array($newType, $params[$key]['type'])) {
                    continue;
                }
                array_push($params[$key]['type'], $parameter->getType());
            }
        }
        return $params;
    }

    /**
     * Set response state
     *
     * @return Zend_Json_Server_Response
     */
    protected function _getReadyResponse()
    {
        $request  = $this->getRequest();
        $response = $this->getResponse();

        $response->setServiceMap($this->getServiceMap());
        if (null !== ($id = $request->getId())) {
            $response->setId($id);
        }
        if (null !== ($version = $request->getVersion())) {
            $response->setVersion($version);
        }

        return $response;
    }

    /**
     * Get method return type
     *
     * @param  Zend_Server_Reflection_Function_Abstract $method
     * @return string|array
     */
    protected function _getReturnType(Zend_Server_Method_Definition $method)
    {
        $return = array();
        foreach ($method->getPrototypes() as $prototype) {
            $return[] = $prototype->getReturnType();
        }
        if (1 == count($return)) {
            return $return[0];
        }
        return $return;
    }

    /**
     * Retrieve list of allowed SMD methods for proxying
     *
     * @return array
     */
    protected function _getSmdMethods()
    {
        if (null === $this->_smdMethods) {
            $this->_smdMethods = array();
            #require_once 'Zend/Json/Server/Smd.php';
            $methods = get_class_methods('Zend_Json_Server_Smd');
            foreach ($methods as $key => $method) {
                if (!preg_match('/^(set|get)/', $method)) {
                    continue;
                }
                if (strstr($method, 'Service')) {
                    continue;
                }
                $this->_smdMethods[] = $method;
            }
        }
        return $this->_smdMethods;
    }

    /**
     * Internal method for handling request
     *
     * @return void
     */
    protected function _handle()
    {
        $request = $this->getRequest();

        if (!$request->isMethodError() && (null === $request->getMethod())) {
            return $this->fault('Invalid Request', -32600);
        }

        if ($request->isMethodError()) {
            return $this->fault('Invalid Request', -32600);
        }

        $method = $request->getMethod();
        if (!$this->_table->hasMethod($method)) {
            return $this->fault('Method not found', -32601);
        }

        $params        = $request->getParams();
        $invocable     = $this->_table->getMethod($method);
        $serviceMap    = $this->getServiceMap();
        $service       = $serviceMap->getService($method);
        $serviceParams = $service->getParams();

        if (count($params) < count($serviceParams)) {
            $params = $this->_getDefaultParams($params, $serviceParams);
        }

        //Make sure named parameters are passed in correct order
        if ( is_string( key( $params ) ) ) {

            $callback = $invocable->getCallback();
            if ('function' == $callback->getType()) {
                $reflection = new ReflectionFunction( $callback->getFunction() );
                $refParams  = $reflection->getParameters();
            } else {
                
                $reflection = new ReflectionMethod( 
                    $callback->getClass(),
                    $callback->getMethod()
                );
                $refParams = $reflection->getParameters();
            }

            $orderedParams = array();
            foreach( $reflection->getParameters() as $refParam ) {
                if( isset( $params[ $refParam->getName() ] ) ) {
                    $orderedParams[ $refParam->getName() ] = $params[ $refParam->getName() ];
                } elseif( $refParam->isOptional() ) {
                    $orderedParams[ $refParam->getName() ] = null;
                } else {
                    throw new Zend_Server_Exception( 
                        'Missing required parameter: ' . $refParam->getName() 
                    ); 
                }
            }
            $params = $orderedParams;
        }

        try {
            $result = $this->_dispatch($invocable, $params);
        } catch (Exception $e) {
            return $this->fault($e->getMessage(), $e->getCode(), $e);
        }

        $this->getResponse()->setResult($result);
    }
}
