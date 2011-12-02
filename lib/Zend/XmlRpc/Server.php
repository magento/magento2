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
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Server.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * Extends Zend_Server_Abstract
 */
#require_once 'Zend/Server/Abstract.php';

/**
 * XMLRPC Request
 */
#require_once 'Zend/XmlRpc/Request.php';

/**
 * XMLRPC Response
 */
#require_once 'Zend/XmlRpc/Response.php';

/**
 * XMLRPC HTTP Response
 */
#require_once 'Zend/XmlRpc/Response/Http.php';

/**
 * XMLRPC server fault class
 */
#require_once 'Zend/XmlRpc/Server/Fault.php';

/**
 * XMLRPC server system methods class
 */
#require_once 'Zend/XmlRpc/Server/System.php';

/**
 * Convert PHP to and from xmlrpc native types
 */
#require_once 'Zend/XmlRpc/Value.php';

/**
 * Reflection API for function/method introspection
 */
#require_once 'Zend/Server/Reflection.php';

/**
 * Zend_Server_Reflection_Function_Abstract
 */
#require_once 'Zend/Server/Reflection/Function/Abstract.php';

/**
 * Specifically grab the Zend_Server_Reflection_Method for manually setting up
 * system.* methods and handling callbacks in {@link loadFunctions()}.
 */
#require_once 'Zend/Server/Reflection/Method.php';

/**
 * An XML-RPC server implementation
 *
 * Example:
 * <code>
 * #require_once 'Zend/XmlRpc/Server.php';
 * #require_once 'Zend/XmlRpc/Server/Cache.php';
 * #require_once 'Zend/XmlRpc/Server/Fault.php';
 * #require_once 'My/Exception.php';
 * #require_once 'My/Fault/Observer.php';
 *
 * // Instantiate server
 * $server = new Zend_XmlRpc_Server();
 *
 * // Allow some exceptions to report as fault responses:
 * Zend_XmlRpc_Server_Fault::attachFaultException('My_Exception');
 * Zend_XmlRpc_Server_Fault::attachObserver('My_Fault_Observer');
 *
 * // Get or build dispatch table:
 * if (!Zend_XmlRpc_Server_Cache::get($filename, $server)) {
 *     #require_once 'Some/Service/Class.php';
 *     #require_once 'Another/Service/Class.php';
 *
 *     // Attach Some_Service_Class in 'some' namespace
 *     $server->setClass('Some_Service_Class', 'some');
 *
 *     // Attach Another_Service_Class in 'another' namespace
 *     $server->setClass('Another_Service_Class', 'another');
 *
 *     // Create dispatch table cache file
 *     Zend_XmlRpc_Server_Cache::save($filename, $server);
 * }
 *
 * $response = $server->handle();
 * echo $response;
 * </code>
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Server extends Zend_Server_Abstract
{
    /**
     * Character encoding
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Request processed
     * @var null|Zend_XmlRpc_Request
     */
    protected $_request = null;

    /**
     * Class to use for responses; defaults to {@link Zend_XmlRpc_Response_Http}
     * @var string
     */
    protected $_responseClass = 'Zend_XmlRpc_Response_Http';

    /**
     * Dispatch table of name => method pairs
     * @var Zend_Server_Definition
     */
    protected $_table;

    /**
     * PHP types => XML-RPC types
     * @var array
     */
    protected $_typeMap = array(
        'i4'                         => 'i4',
        'int'                        => 'int',
        'integer'                    => 'int',
        'Zend_Crypt_Math_BigInteger' => 'i8',
        'i8'                         => 'i8',
        'ex:i8'                      => 'i8',
        'double'                     => 'double',
        'float'                      => 'double',
        'real'                       => 'double',
        'boolean'                    => 'boolean',
        'bool'                       => 'boolean',
        'true'                       => 'boolean',
        'false'                      => 'boolean',
        'string'                     => 'string',
        'str'                        => 'string',
        'base64'                     => 'base64',
        'dateTime.iso8601'           => 'dateTime.iso8601',
        'date'                       => 'dateTime.iso8601',
        'time'                       => 'dateTime.iso8601',
        'time'                       => 'dateTime.iso8601',
        'Zend_Date'                  => 'dateTime.iso8601',
        'DateTime'                   => 'dateTime.iso8601',
        'array'                      => 'array',
        'struct'                     => 'struct',
        'null'                       => 'nil',
        'nil'                        => 'nil',
        'ex:nil'                     => 'nil',
        'void'                       => 'void',
        'mixed'                      => 'struct',
    );

    /**
     * Send arguments to all methods or just constructor?
     *
     * @var bool
     */
    protected $_sendArgumentsToAllMethods = true;

    /**
     * Constructor
     *
     * Creates system.* methods.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_table = new Zend_Server_Definition();
        $this->_registerSystemMethods();
    }

    /**
     * Proxy calls to system object
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function __call($method, $params)
    {
        $system = $this->getSystem();
        if (!method_exists($system, $method)) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Unknown instance method called on server: ' . $method);
        }
        return call_user_func_array(array($system, $method), $params);
    }

    /**
     * Attach a callback as an XMLRPC method
     *
     * Attaches a callback as an XMLRPC method, prefixing the XMLRPC method name
     * with $namespace, if provided. Reflection is done on the callback's
     * docblock to create the methodHelp for the XMLRPC method.
     *
     * Additional arguments to pass to the function at dispatch may be passed;
     * any arguments following the namespace will be aggregated and passed at
     * dispatch time.
     *
     * @param string|array $function Valid callback
     * @param string $namespace Optional namespace prefix
     * @return void
     * @throws Zend_XmlRpc_Server_Exception
     */
    public function addFunction($function, $namespace = '')
    {
        if (!is_string($function) && !is_array($function)) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Unable to attach function; invalid', 611);
        }

        $argv = null;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 2);
        }

        $function = (array) $function;
        foreach ($function as $func) {
            if (!is_string($func) || !function_exists($func)) {
                #require_once 'Zend/XmlRpc/Server/Exception.php';
                throw new Zend_XmlRpc_Server_Exception('Unable to attach function; invalid', 611);
            }
            $reflection = Zend_Server_Reflection::reflectFunction($func, $argv, $namespace);
            $this->_buildSignature($reflection);
        }
    }

    /**
     * Attach class methods as XMLRPC method handlers
     *
     * $class may be either a class name or an object. Reflection is done on the
     * class or object to determine the available public methods, and each is
     * attached to the server as an available method; if a $namespace has been
     * provided, that namespace is used to prefix the XMLRPC method names.
     *
     * Any additional arguments beyond $namespace will be passed to a method at
     * invocation.
     *
     * @param string|object $class
     * @param string $namespace Optional
     * @param mixed $argv Optional arguments to pass to methods
     * @return void
     * @throws Zend_XmlRpc_Server_Exception on invalid input
     */
    public function setClass($class, $namespace = '', $argv = null)
    {
        if (is_string($class) && !class_exists($class)) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Invalid method class', 610);
        }

        $argv = null;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $argv = array_slice($argv, 2);
        }

        $dispatchable = Zend_Server_Reflection::reflectClass($class, $argv, $namespace);
        foreach ($dispatchable->getMethods() as $reflection) {
            $this->_buildSignature($reflection, $class);
        }
    }

    /**
     * Raise an xmlrpc server fault
     *
     * @param string|Exception $fault
     * @param int $code
     * @return Zend_XmlRpc_Server_Fault
     */
    public function fault($fault = null, $code = 404)
    {
        if (!$fault instanceof Exception) {
            $fault = (string) $fault;
            if (empty($fault)) {
                $fault = 'Unknown Error';
            }
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            $fault = new Zend_XmlRpc_Server_Exception($fault, $code);
        }

        return Zend_XmlRpc_Server_Fault::getInstance($fault);
    }

    /**
     * Handle an xmlrpc call
     *
     * @param Zend_XmlRpc_Request $request Optional
     * @return Zend_XmlRpc_Response|Zend_XmlRpc_Fault
     */
    public function handle($request = false)
    {
        // Get request
        if ((!$request || !$request instanceof Zend_XmlRpc_Request)
            && (null === ($request = $this->getRequest()))
        ) {
            #require_once 'Zend/XmlRpc/Request/Http.php';
            $request = new Zend_XmlRpc_Request_Http();
            $request->setEncoding($this->getEncoding());
        }

        $this->setRequest($request);

        if ($request->isFault()) {
            $response = $request->getFault();
        } else {
            try {
                $response = $this->_handle($request);
            } catch (Exception $e) {
                $response = $this->fault($e);
            }
        }

        // Set output encoding
        $response->setEncoding($this->getEncoding());

        return $response;
    }

    /**
     * Load methods as returned from {@link getFunctions}
     *
     * Typically, you will not use this method; it will be called using the
     * results pulled from {@link Zend_XmlRpc_Server_Cache::get()}.
     *
     * @param  array|Zend_Server_Definition $definition
     * @return void
     * @throws Zend_XmlRpc_Server_Exception on invalid input
     */
    public function loadFunctions($definition)
    {
        if (!is_array($definition) && (!$definition instanceof Zend_Server_Definition)) {
            if (is_object($definition)) {
                $type = get_class($definition);
            } else {
                $type = gettype($definition);
            }
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Unable to load server definition; must be an array or Zend_Server_Definition, received ' . $type, 612);
        }

        $this->_table->clearMethods();
        $this->_registerSystemMethods();

        if ($definition instanceof Zend_Server_Definition) {
            $definition = $definition->getMethods();
        }

        foreach ($definition as $key => $method) {
            if ('system.' == substr($key, 0, 7)) {
                continue;
            }
            $this->_table->addMethod($method, $key);
        }
    }

    /**
     * Set encoding
     *
     * @param string $encoding
     * @return Zend_XmlRpc_Server
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        Zend_XmlRpc_Value::setEncoding($encoding);
        return $this;
    }

    /**
     * Retrieve current encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Do nothing; persistence is handled via {@link Zend_XmlRpc_Server_Cache}
     *
     * @param  mixed $mode
     * @return void
     */
    public function setPersistence($mode)
    {
    }

    /**
     * Set the request object
     *
     * @param string|Zend_XmlRpc_Request $request
     * @return Zend_XmlRpc_Server
     * @throws Zend_XmlRpc_Server_Exception on invalid request class or object
     */
    public function setRequest($request)
    {
        if (is_string($request) && class_exists($request)) {
            $request = new $request();
            if (!$request instanceof Zend_XmlRpc_Request) {
                #require_once 'Zend/XmlRpc/Server/Exception.php';
                throw new Zend_XmlRpc_Server_Exception('Invalid request class');
            }
            $request->setEncoding($this->getEncoding());
        } elseif (!$request instanceof Zend_XmlRpc_Request) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Invalid request object');
        }

        $this->_request = $request;
        return $this;
    }

    /**
     * Return currently registered request object
     *
     * @return null|Zend_XmlRpc_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set the class to use for the response
     *
     * @param string $class
     * @return boolean True if class was set, false if not
     */
    public function setResponseClass($class)
    {
        if (!class_exists($class) or
            ($c = new ReflectionClass($class) and !$c->isSubclassOf('Zend_XmlRpc_Response'))) {

            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Invalid response class');
        }
        $this->_responseClass = $class;
        return true;
    }

    /**
     * Retrieve current response class
     *
     * @return string
     */
    public function getResponseClass()
    {
        return $this->_responseClass;
    }

    /**
     * Retrieve dispatch table
     *
     * @return array
     */
    public function getDispatchTable()
    {
        return $this->_table;
    }

    /**
     * Returns a list of registered methods
     *
     * Returns an array of dispatchables (Zend_Server_Reflection_Function,
     * _Method, and _Class items).
     *
     * @return array
     */
    public function getFunctions()
    {
        return $this->_table->toArray();
    }

    /**
     * Retrieve system object
     *
     * @return Zend_XmlRpc_Server_System
     */
    public function getSystem()
    {
        return $this->_system;
    }

    /**
     * Send arguments to all methods?
     *
     * If setClass() is used to add classes to the server, this flag defined
     * how to handle arguments. If set to true, all methods including constructor
     * will receive the arguments. If set to false, only constructor will receive the
     * arguments
     */
    public function sendArgumentsToAllMethods($flag = null)
    {
        if ($flag === null) {
            return $this->_sendArgumentsToAllMethods;
        }

        $this->_sendArgumentsToAllMethods = (bool)$flag;
        return $this;
    }

    /**
     * Map PHP type to XML-RPC type
     *
     * @param  string $type
     * @return string
     */
    protected function _fixType($type)
    {
        if (isset($this->_typeMap[$type])) {
            return $this->_typeMap[$type];
        }
        return 'void';
    }

    /**
     * Handle an xmlrpc call (actual work)
     *
     * @param Zend_XmlRpc_Request $request
     * @return Zend_XmlRpc_Response
     * @throws Zend_XmlRpcServer_Exception|Exception
     * Zend_XmlRpcServer_Exceptions are thrown for internal errors; otherwise,
     * any other exception may be thrown by the callback
     */
    protected function _handle(Zend_XmlRpc_Request $request)
    {
        $method = $request->getMethod();

        // Check for valid method
        if (!$this->_table->hasMethod($method)) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Method "' . $method . '" does not exist', 620);
        }

        $info     = $this->_table->getMethod($method);
        $params   = $request->getParams();
        $argv     = $info->getInvokeArguments();
        if (0 < count($argv) and $this->sendArgumentsToAllMethods()) {
            $params = array_merge($params, $argv);
        }

        // Check calling parameters against signatures
        $matched    = false;
        $sigCalled  = $request->getTypes();

        $sigLength  = count($sigCalled);
        $paramsLen  = count($params);
        if ($sigLength < $paramsLen) {
            for ($i = $sigLength; $i < $paramsLen; ++$i) {
                $xmlRpcValue = Zend_XmlRpc_Value::getXmlRpcValue($params[$i]);
                $sigCalled[] = $xmlRpcValue->getType();
            }
        }

        $signatures = $info->getPrototypes();
        foreach ($signatures as $signature) {
            $sigParams = $signature->getParameters();
            if ($sigCalled === $sigParams) {
                $matched = true;
                break;
            }
        }
        if (!$matched) {
            #require_once 'Zend/XmlRpc/Server/Exception.php';
            throw new Zend_XmlRpc_Server_Exception('Calling parameters do not match signature', 623);
        }

        $return        = $this->_dispatch($info, $params);
        $responseClass = $this->getResponseClass();
        return new $responseClass($return);
    }

    /**
     * Register system methods with the server
     *
     * @return void
     */
    protected function _registerSystemMethods()
    {
        $system = new Zend_XmlRpc_Server_System($this);
        $this->_system = $system;
        $this->setClass($system, 'system');
    }
}
