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
 * @package    Zend_Soap
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Server_Interface
 */
#require_once 'Zend/Server/Interface.php';

/** @see Zend_Xml_Security */
#require_once 'Zend/Xml/Security.php';

/** @see Zend_Xml_Exception */
#require_once 'Zend/Xml/Exception.php';

/**
 * Zend_Soap_Server
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage Server
 * @uses       Zend_Server_Interface
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Soap_Server implements Zend_Server_Interface
{
    /**
     * Actor URI
     * @var string URI
     */
    protected $_actor;

    /**
     * Class registered with this server
     * @var string
     */
    protected $_class;

    /**
     * Arguments to pass to {@link $_class} constructor
     * @var array
     */
    protected $_classArgs = array();

    /**
     * Object registered with this server
     */
    protected $_object;

    /**
     * Array of SOAP type => PHP class pairings for handling return/incoming values
     * @var array
     */
    protected $_classmap;

    /**
     * Encoding
     * @var string
     */
    protected $_encoding;

    /**
     * SOAP Server Features
     *
     * @var int
     */
    protected $_features;

    /**
     * WSDL Caching Options of SOAP Server
     *
     * @var mixed
     */
    protected $_wsdlCache;

    /**
     * WS-I compliant
     *
     * @var boolean
     */
    protected $_wsiCompliant;

    /**
     * Registered fault exceptions
     * @var array
     */
    protected $_faultExceptions = array();

    /**
     * Functions registered with this server; may be either an array or the SOAP_FUNCTIONS_ALL
     * constant
     * @var array|int
     */
    protected $_functions = array();

    /**
     * Persistence mode; should be one of the SOAP persistence constants
     * @var int
     */
    protected $_persistence;

    /**
     * Request XML
     * @var string
     */
    protected $_request;

    /**
     * Response XML
     * @var string
     */
    protected $_response;

    /**
     * Flag: whether or not {@link handle()} should return a response instead
     * of automatically emitting it.
     * @var boolean
     */
    protected $_returnResponse = false;

    /**
     * SOAP version to use; SOAP_1_2 by default, to allow processing of headers
     * @var int
     */
    protected $_soapVersion = SOAP_1_2;

    /**
     * URI or path to WSDL
     * @var string
     */
    protected $_wsdl;

    /**
     * URI namespace for SOAP server
     * @var string URI
     */
    protected $_uri;

    /**
     * Constructor
     *
     * Sets display_errors INI setting to off (prevent client errors due to bad
     * XML in response). Registers {@link handlePhpErrors()} as error handler
     * for E_USER_ERROR.
     *
     * If $wsdl is provided, it is passed on to {@link setWsdl()}; if any
     * options are specified, they are passed on to {@link setOptions()}.
     *
     * @param string $wsdl
     * @param array $options
     * @return void
     */
    public function __construct($wsdl = null, array $options = null)
    {
        if (!extension_loaded('soap')) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('SOAP extension is not loaded.');
        }

        if (null !== $wsdl) {
            $this->setWsdl($wsdl);
        }

        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set Options
     *
     * Allows setting options as an associative array of option => value pairs.
     *
     * @param  array|Zend_Config $options
     * @return Zend_Soap_Server
     */
    public function setOptions($options)
    {
        if($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'actor':
                    $this->setActor($value);
                    break;
                case 'classmap':
                case 'classMap':
                    $this->setClassmap($value);
                    break;
                case 'encoding':
                    $this->setEncoding($value);
                    break;
                case 'soapVersion':
                case 'soap_version':
                    $this->setSoapVersion($value);
                    break;
                case 'uri':
                    $this->setUri($value);
                    break;
                case 'wsdl':
                    $this->setWsdl($value);
                    break;
                case 'featues':
                    trigger_error(__METHOD__ . ': the option "featues" is deprecated as of 1.10.x and will be removed with 2.0.0; use "features" instead', E_USER_NOTICE);
                case 'features':
                    $this->setSoapFeatures($value);
                    break;
                case 'cache_wsdl':
                    $this->setWsdlCache($value);
                    break;
                case 'wsi_compliant':
                    $this->setWsiCompliant($value);
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    /**
     * Return array of options suitable for using with SoapServer constructor
     *
     * @return array
     */
    public function getOptions()
    {
        $options = array();
        if (null !== $this->_actor) {
            $options['actor'] = $this->_actor;
        }

        if (null !== $this->_classmap) {
            $options['classmap'] = $this->_classmap;
        }

        if (null !== $this->_encoding) {
            $options['encoding'] = $this->_encoding;
        }

        if (null !== $this->_soapVersion) {
            $options['soap_version'] = $this->_soapVersion;
        }

        if (null !== $this->_uri) {
            $options['uri'] = $this->_uri;
        }

        if (null !== $this->_features) {
            $options['features'] = $this->_features;
        }

        if (null !== $this->_wsdlCache) {
            $options['cache_wsdl'] = $this->_wsdlCache;
        }

        if (null !== $this->_wsiCompliant) {
            $options['wsi_compliant'] = $this->_wsiCompliant;
        }

        return $options;
    }
    /**
     * Set WS-I compliant
     *
     * @param  boolean $value
     * @return Zend_Soap_Server
     */
    public function setWsiCompliant($value)
    {
        if (is_bool($value)) {
            $this->_wsiCompliant = $value;
        }
        return $this;
    }
    /**
     * Gt WS-I compliant
     *
     * @return boolean
     */
    public function getWsiCompliant()
    {
        return $this->_wsiCompliant;
    }
    /**
     * Set encoding
     *
     * @param  string $encoding
     * @return Zend_Soap_Server
     * @throws Zend_Soap_Server_Exception with invalid encoding argument
     */
    public function setEncoding($encoding)
    {
        if (!is_string($encoding)) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid encoding specified');
        }

        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set SOAP version
     *
     * @param  int $version One of the SOAP_1_1 or SOAP_1_2 constants
     * @return Zend_Soap_Server
     * @throws Zend_Soap_Server_Exception with invalid soap version argument
     */
    public function setSoapVersion($version)
    {
        if (!in_array($version, array(SOAP_1_1, SOAP_1_2))) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid soap version specified');
        }

        $this->_soapVersion = $version;
        return $this;
    }

    /**
     * Get SOAP version
     *
     * @return int
     */
    public function getSoapVersion()
    {
        return $this->_soapVersion;
    }

    /**
     * Check for valid URN
     *
     * @param  string $urn
     * @return true
     * @throws Zend_Soap_Server_Exception on invalid URN
     */
    public function validateUrn($urn)
    {
        $scheme = parse_url($urn, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid URN');
        }

        return true;
    }

    /**
     * Set actor
     *
     * Actor is the actor URI for the server.
     *
     * @param  string $actor
     * @return Zend_Soap_Server
     */
    public function setActor($actor)
    {
        $this->validateUrn($actor);
        $this->_actor = $actor;
        return $this;
    }

    /**
     * Retrieve actor
     *
     * @return string
     */
    public function getActor()
    {
        return $this->_actor;
    }

    /**
     * Set URI
     *
     * URI in SoapServer is actually the target namespace, not a URI; $uri must begin with 'urn:'.
     *
     * @param  string $uri
     * @return Zend_Soap_Server
     * @throws Zend_Soap_Server_Exception with invalid uri argument
     */
    public function setUri($uri)
    {
        $this->validateUrn($uri);
        $this->_uri = $uri;
        return $this;
    }

    /**
     * Retrieve URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Set classmap
     *
     * @param  array $classmap
     * @return Zend_Soap_Server
     * @throws Zend_Soap_Server_Exception for any invalid class in the class map
     */
    public function setClassmap($classmap)
    {
        if (!is_array($classmap)) {
            /**
             * @see Zend_Soap_Server_Exception
             */
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Classmap must be an array');
        }
        foreach ($classmap as $type => $class) {
            if (!class_exists($class)) {
                /**
                 * @see Zend_Soap_Server_Exception
                 */
                #require_once 'Zend/Soap/Server/Exception.php';
                throw new Zend_Soap_Server_Exception('Invalid class in class map');
            }
        }

        $this->_classmap = $classmap;
        return $this;
    }

    /**
     * Retrieve classmap
     *
     * @return mixed
     */
    public function getClassmap()
    {
        return $this->_classmap;
    }

    /**
     * Set wsdl
     *
     * @param string $wsdl  URI or path to a WSDL
     * @return Zend_Soap_Server
     */
    public function setWsdl($wsdl)
    {
        $this->_wsdl = $wsdl;
        return $this;
    }

    /**
     * Retrieve wsdl
     *
     * @return string
     */
    public function getWsdl()
    {
        return $this->_wsdl;
    }

    /**
     * Set the SOAP Feature options.
     *
     * @param  string|int $feature
     * @return Zend_Soap_Server
     */
    public function setSoapFeatures($feature)
    {
        $this->_features = $feature;
        return $this;
    }

    /**
     * Return current SOAP Features options
     *
     * @return int
     */
    public function getSoapFeatures()
    {
        return $this->_features;
    }

    /**
     * Set the SOAP Wsdl Caching Options
     *
     * @param string|int|boolean $caching
     * @return Zend_Soap_Server
     */
    public function setWsdlCache($options)
    {
        $this->_wsdlCache = $options;
        return $this;
    }

    /**
     * Get current SOAP Wsdl Caching option
     */
    public function getWsdlCache()
    {
        return $this->_wsdlCache;
    }

    /**
     * Attach a function as a server method
     *
     * @param array|string $function Function name, array of function names to attach,
     * or SOAP_FUNCTIONS_ALL to attach all functions
     * @param  string $namespace Ignored
     * @return Zend_Soap_Server
     * @throws Zend_Soap_Server_Exception on invalid functions
     */
    public function addFunction($function, $namespace = '')
    {
        // Bail early if set to SOAP_FUNCTIONS_ALL
        if ($this->_functions == SOAP_FUNCTIONS_ALL) {
            return $this;
        }

        if (is_array($function)) {
            foreach ($function as $func) {
                if (is_string($func) && function_exists($func)) {
                    $this->_functions[] = $func;
                } else {
                    #require_once 'Zend/Soap/Server/Exception.php';
                    throw new Zend_Soap_Server_Exception('One or more invalid functions specified in array');
                }
            }
            $this->_functions = array_merge($this->_functions, $function);
        } elseif (is_string($function) && function_exists($function)) {
            $this->_functions[] = $function;
        } elseif ($function == SOAP_FUNCTIONS_ALL) {
            $this->_functions = SOAP_FUNCTIONS_ALL;
        } else {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid function specified');
        }

        if (is_array($this->_functions)) {
            $this->_functions = array_unique($this->_functions);
        }

        return $this;
    }

    /**
     * Attach a class to a server
     *
     * Accepts a class name to use when handling requests. Any additional
     * arguments will be passed to that class' constructor when instantiated.
     *
     * See {@link setObject()} to set preconfigured object instances as request handlers.
     *
     * @param string $class Class Name which executes SOAP Requests at endpoint.
     * @return Zend_Soap_Server
     * @throws Zend_Soap_Server_Exception if called more than once, or if class
     * does not exist
     */
    public function setClass($class, $namespace = '', $argv = null)
    {
        if (isset($this->_class)) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('A class has already been registered with this soap server instance');
        }

        if (!is_string($class)) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid class argument (' . gettype($class) . ')');
        }

        if (!class_exists($class)) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Class "' . $class . '" does not exist');
        }

        $this->_class = $class;
        if (1 < func_num_args()) {
            $argv = func_get_args();
            array_shift($argv);
            $this->_classArgs = $argv;
        }

        return $this;
    }

    /**
     * Attach an object to a server
     *
     * Accepts an instanciated object to use when handling requests.
     *
     * @param object $object
     * @return Zend_Soap_Server
     */
    public function setObject($object)
    {
        if(!is_object($object)) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid object argument ('.gettype($object).')');
        }

        if(isset($this->_object)) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('An object has already been registered with this soap server instance');
        }

        if ($this->_wsiCompliant) {
            #require_once 'Zend/Soap/Server/Proxy.php';
            $this->_object = new Zend_Soap_Server_Proxy($object);
        } else {
            $this->_object = $object;
        }

        return $this;
    }

    /**
     * Return a server definition array
     *
     * Returns a list of all functions registered with {@link addFunction()},
     * merged with all public methods of the class set with {@link setClass()}
     * (if any).
     *
     * @access public
     * @return array
     */
    public function getFunctions()
    {
        $functions = array();
        if (null !== $this->_class) {
            $functions = get_class_methods($this->_class);
        } elseif (null !== $this->_object) {
            $functions = get_class_methods($this->_object);
        }

        return array_merge((array) $this->_functions, $functions);
    }

    /**
     * Unimplemented: Load server definition
     *
     * @param array $array
     * @return void
     * @throws Zend_Soap_Server_Exception Unimplemented
     */
    public function loadFunctions($definition)
    {
        #require_once 'Zend/Soap/Server/Exception.php';
        throw new Zend_Soap_Server_Exception('Unimplemented');
    }

    /**
     * Set server persistence
     *
     * @param int $mode
     * @return Zend_Soap_Server
     */
    public function setPersistence($mode)
    {
        if (!in_array($mode, array(SOAP_PERSISTENCE_SESSION, SOAP_PERSISTENCE_REQUEST))) {
            #require_once 'Zend/Soap/Server/Exception.php';
            throw new Zend_Soap_Server_Exception('Invalid persistence mode specified');
        }

        $this->_persistence = $mode;
        return $this;
    }

    /**
     * Get server persistence
     *
     * @return Zend_Soap_Server
     */
    public function getPersistence()
    {
        return $this->_persistence;
    }

    /**
     * Set request
     *
     * $request may be any of:
     * - DOMDocument; if so, then cast to XML
     * - DOMNode; if so, then grab owner document and cast to XML
     * - SimpleXMLElement; if so, then cast to XML
     * - stdClass; if so, calls __toString() and verifies XML
     * - string; if so, verifies XML
     *
     * @param DOMDocument|DOMNode|SimpleXMLElement|stdClass|string $request
     * @return Zend_Soap_Server
     */
    protected function _setRequest($request)
    {
        if ($request instanceof DOMDocument) {
            $xml = $request->saveXML();
        } elseif ($request instanceof DOMNode) {
            $xml = $request->ownerDocument->saveXML();
        } elseif ($request instanceof SimpleXMLElement) {
            $xml = $request->asXML();
        } elseif (is_object($request) || is_string($request)) {
            if (is_object($request)) {
                $xml = $request->__toString();
            } else {
                $xml = $request;
            }

            $dom = new DOMDocument();
            try {
                if(strlen($xml) == 0 || (!$dom = Zend_Xml_Security::scan($xml, $dom))) {
                    #require_once 'Zend/Soap/Server/Exception.php';
                    throw new Zend_Soap_Server_Exception('Invalid XML');
                }
            } catch (Zend_Xml_Exception $e) {
                #require_once 'Zend/Soap/Server/Exception.php';
                throw new Zend_Soap_Server_Exception(
                    $e->getMessage()
                );
            }
        }
        $this->_request = $xml;
        return $this;
    }

    /**
     * Retrieve request XML
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->_request;
    }

    /**
     * Set return response flag
     *
     * If true, {@link handle()} will return the response instead of
     * automatically sending it back to the requesting client.
     *
     * The response is always available via {@link getResponse()}.
     *
     * @param boolean $flag
     * @return Zend_Soap_Server
     */
    public function setReturnResponse($flag)
    {
        $this->_returnResponse = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve return response flag
     *
     * @return boolean
     */
    public function getReturnResponse()
    {
        return $this->_returnResponse;
    }

    /**
     * Get response XML
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->_response;
    }

    /**
     * Get SoapServer object
     *
     * Uses {@link $_wsdl} and return value of {@link getOptions()} to instantiate
     * SoapServer object, and then registers any functions or class with it, as
     * well as peristence.
     *
     * @return SoapServer
     */
    protected function _getSoap()
    {
        $options = $this->getOptions();
        $server  = new SoapServer($this->_wsdl, $options);

        if (!empty($this->_functions)) {
            $server->addFunction($this->_functions);
        }

        if (!empty($this->_class)) {
            $args = $this->_classArgs;
            array_unshift($args, $this->_class);
            if ($this->_wsiCompliant) {
                #require_once 'Zend/Soap/Server/Proxy.php';
                array_unshift($args, 'Zend_Soap_Server_Proxy');
            }
            call_user_func_array(array($server, 'setClass'), $args);
        }

        if (!empty($this->_object)) {
            $server->setObject($this->_object);
        }

        if (null !== $this->_persistence) {
            $server->setPersistence($this->_persistence);
        }

        return $server;
    }

    /**
     * Handle a request
     *
     * Instantiates SoapServer object with options set in object, and
     * dispatches its handle() method.
     *
     * $request may be any of:
     * - DOMDocument; if so, then cast to XML
     * - DOMNode; if so, then grab owner document and cast to XML
     * - SimpleXMLElement; if so, then cast to XML
     * - stdClass; if so, calls __toString() and verifies XML
     * - string; if so, verifies XML
     *
     * If no request is passed, pulls request using php:://input (for
     * cross-platform compatability purposes).
     *
     * @param DOMDocument|DOMNode|SimpleXMLElement|stdClass|string $request Optional request
     * @return void|string
     */
    public function handle($request = null)
    {
        if (null === $request) {
            $request = file_get_contents('php://input');
        }

        // Set Zend_Soap_Server error handler
        $displayErrorsOriginalState = $this->_initializeSoapErrorContext();

        $setRequestException = null;
        /**
         * @see Zend_Soap_Server_Exception
         */
        #require_once 'Zend/Soap/Server/Exception.php';
        try {
            $this->_setRequest($request);
        } catch (Zend_Soap_Server_Exception $e) {
            $setRequestException = $e;
        }

        $soap = $this->_getSoap();

        $fault = false;
        ob_start();
        if ($setRequestException instanceof Exception) {
            // Create SOAP fault message if we've caught a request exception
            $fault = $this->fault($setRequestException->getMessage(), 'Sender');
        } else {
            try {
                $soap->handle($this->_request);
            } catch (Exception $e) {
                $fault = $this->fault($e);
            }
        }
        $this->_response = ob_get_clean();

        // Restore original error handler
        restore_error_handler();
        ini_set('display_errors', $displayErrorsOriginalState);

        // Send a fault, if we have one
        if ($fault) {
            $soap->fault($fault->faultcode, $fault->faultstring);
        }

        if (!$this->_returnResponse) {
            echo $this->_response;
            return;
        }

        return $this->_response;
    }

    /**
     * Method initalizes the error context that the SOAPServer enviroment will run in.
     *
     * @return boolean display_errors original value
     */
    protected function _initializeSoapErrorContext()
    {
        $displayErrorsOriginalState = ini_get('display_errors');
        ini_set('display_errors', false);
        set_error_handler(array($this, 'handlePhpErrors'), E_USER_ERROR);
        return $displayErrorsOriginalState;
    }

    /**
     * Register a valid fault exception
     *
     * @param  string|array $class Exception class or array of exception classes
     * @return Zend_Soap_Server
     */
    public function registerFaultException($class)
    {
        $this->_faultExceptions = array_merge($this->_faultExceptions, (array) $class);
        return $this;
    }

    /**
     * Deregister a fault exception from the fault exception stack
     *
     * @param  string $class
     * @return boolean
     */
    public function deregisterFaultException($class)
    {
        if (in_array($class, $this->_faultExceptions, true)) {
            $index = array_search($class, $this->_faultExceptions);
            unset($this->_faultExceptions[$index]);
            return true;
        }

        return false;
    }

    /**
     * Return fault exceptions list
     *
     * @return array
     */
    public function getFaultExceptions()
    {
        return $this->_faultExceptions;
    }

    /**
     * Generate a server fault
     *
     * Note that the arguments are reverse to those of SoapFault.
     *
     * If an exception is passed as the first argument, its message and code
     * will be used to create the fault object if it has been registered via
     * {@Link registerFaultException()}.
     *
     * @link   http://www.w3.org/TR/soap12-part1/#faultcodes
     * @param  string|Exception $fault
     * @param  string $code SOAP Fault Codes
     * @return SoapFault
     */
    public function fault($fault = null, $code = "Receiver")
    {
        if ($fault instanceof Exception) {
            $class = get_class($fault);
            if (in_array($class, $this->_faultExceptions)) {
                $message = $fault->getMessage();
                $eCode   = $fault->getCode();
                $code    = empty($eCode) ? $code : $eCode;
            } else {
                $message = 'Unknown error';
            }
        } elseif(is_string($fault)) {
            $message = $fault;
        } else {
            $message = 'Unknown error';
        }

        $allowedFaultModes = array(
            'VersionMismatch', 'MustUnderstand', 'DataEncodingUnknown',
            'Sender', 'Receiver', 'Server'
        );
        if(!in_array($code, $allowedFaultModes)) {
            $code = "Receiver";
        }

        return new SoapFault($code, $message);
    }

    /**
     * Throw PHP errors as SoapFaults
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @param array $errcontext
     * @return void
     * @throws SoapFault
     */
    public function handlePhpErrors($errno, $errstr, $errfile = null, $errline = null, array $errcontext = null)
    {
        throw $this->fault($errstr, "Receiver");
    }
}
