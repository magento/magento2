<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Soap;

use SoapServer;
use SoapFault;
use Traversable;
use DOMDocument;
use DOMNode;
use SimpleXMLElement;
use ReflectionClass;
use Zend\Server\Server as ZendServerServer;
use Zend\Stdlib\ArrayUtils;

class Server implements ZendServerServer
{
    /**
     * Actor URI
     * @var string URI
     */
    protected $actor;

    /**
     * Class registered with this server
     * @var string
     */
    protected $class;

    /**
     * Server instance
     * @var SoapServer
     */
    protected $server = null;
    /**
     * Arguments to pass to {@link $class} constructor
     * @var array
     */
    protected $classArgs = array();

    /**
     * Array of SOAP type => PHP class pairings for handling return/incoming values
     * @var array
     */
    protected $classmap;

    /**
     * Encoding
     * @var string
     */
    protected $encoding;

    /**
     * Registered fault exceptions
     * @var array
     */
    protected $faultExceptions = array();

    /**
     * Container for caught exception during business code execution
     * @var \Exception
     */
    protected $caughtException = null;

    /**
     * SOAP Server Features
     * @var int
     */
    protected $features;

    /**
     * Functions registered with this server; may be either an array or the SOAP_FUNCTIONS_ALL constant
     * @var array|int
     */
    protected $functions = array();

    /**
     * Object registered with this server
     */
    protected $object;

    /**
     * Informs if the soap server is in debug mode
     * @var bool
     */
    protected $debug = false;

    /**
     * Persistence mode; should be one of the SOAP persistence constants
     * @var int
     */
    protected $persistence;

    /**
     * Request XML
     * @var string
     */
    protected $request;

    /**
     * Response XML
     * @var string
     */
    protected $response;

    /**
     * Flag: whether or not {@link handle()} should return a response instead of automatically emitting it.
     * @var bool
     */
    protected $returnResponse = false;

    /**
     * SOAP version to use; SOAP_1_2 by default, to allow processing of headers
     * @var int
     */
    protected $soapVersion = SOAP_1_2;

    /**
     * Array of type mappings
     * @var array
     */
    protected $typemap;

    /**
     * URI namespace for SOAP server
     * @var string URI
     */
    protected $uri;

    /**
     * URI or path to WSDL
     * @var string
     */
    protected $wsdl;

    /**
     * WSDL Caching Options of SOAP Server
     * @var mixed
     */
    protected $wsdlCache;

    /**
     * Constructor
     *
     * Sets display_errors INI setting to off (prevent client errors due to bad
     * XML in response). Registers {@link handlePhpErrors()} as error handler
     * for E_USER_ERROR.
     *
     * If $wsdl is provided, it is passed on to {@link setWSDL()}; if any
     * options are specified, they are passed on to {@link setOptions()}.
     *
     * @param  string $wsdl
     * @param  array $options
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct($wsdl = null, array $options = null)
    {
        if (!extension_loaded('soap')) {
            throw new Exception\ExtensionNotLoadedException('SOAP extension is not loaded.');
        }

        if (null !== $wsdl) {
            $this->setWSDL($wsdl);
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
     * @param  array|\Traversable $options
     * @return self
     */
    public function setOptions($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'actor':
                    $this->setActor($value);
                    break;

                case 'classmap':
                case 'class_map':
                    $this->setClassmap($value);
                    break;

                case 'typemap':
                case 'type_map':
                    $this->setTypemap($value);
                    break;

                case 'encoding':
                    $this->setEncoding($value);
                    break;

                case 'soapversion':
                case 'soap_version':
                    $this->setSoapVersion($value);
                    break;

                case 'uri':
                    $this->setUri($value);
                    break;

                case 'wsdl':
                    $this->setWSDL($value);
                    break;

                case 'cache_wsdl':
                    $this->setWSDLCache($value);
                    break;

                case 'features':
                    $this->setSoapFeatures($value);
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
        if (null !== $this->actor) {
            $options['actor'] = $this->getActor();
        }

        if (null !== $this->classmap) {
            $options['classmap'] = $this->getClassmap();
        }

        if (null !== $this->typemap) {
            $options['typemap'] = $this->getTypemap();
        }

        if (null !== $this->encoding) {
            $options['encoding'] = $this->getEncoding();
        }

        if (null !== $this->soapVersion) {
            $options['soap_version'] = $this->getSoapVersion();
        }

        if (null !== $this->uri) {
            $options['uri'] = $this->getUri();
        }

        if (null !== $this->features) {
            $options['features'] = $this->getSoapFeatures();
        }

        if (null !== $this->wsdlCache) {
            $options['cache_wsdl'] = $this->getWSDLCache();
        }

        return $options;
    }

    /**
     * Set encoding
     *
     * @param  string $encoding
     * @return self
     * @throws Exception\InvalidArgumentException with invalid encoding argument
     */
    public function setEncoding($encoding)
    {
        if (!is_string($encoding)) {
            throw new Exception\InvalidArgumentException('Invalid encoding specified');
        }

        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set SOAP version
     *
     * @param  int $version One of the SOAP_1_1 or SOAP_1_2 constants
     * @return self
     * @throws Exception\InvalidArgumentException with invalid soap version argument
     */
    public function setSoapVersion($version)
    {
        if (!in_array($version, array(SOAP_1_1, SOAP_1_2))) {
            throw new Exception\InvalidArgumentException('Invalid soap version specified');
        }

        $this->soapVersion = $version;
        return $this;
    }

    /**
     * Get SOAP version
     *
     * @return int
     */
    public function getSoapVersion()
    {
        return $this->soapVersion;
    }

    /**
     * Check for valid URN
     *
     * @param  string $urn
     * @return true
     * @throws Exception\InvalidArgumentException on invalid URN
     */
    public function validateUrn($urn)
    {
        $scheme = parse_url($urn, PHP_URL_SCHEME);
        if ($scheme === false || $scheme === null) {
            throw new Exception\InvalidArgumentException('Invalid URN');
        }

        return true;
    }

    /**
     * Set actor
     *
     * Actor is the actor URI for the server.
     *
     * @param  string $actor
     * @return self
     */
    public function setActor($actor)
    {
        $this->validateUrn($actor);
        $this->actor = $actor;
        return $this;
    }

    /**
     * Retrieve actor
     *
     * @return string
     */
    public function getActor()
    {
        return $this->actor;
    }

    /**
     * Set URI
     *
     * URI in SoapServer is actually the target namespace, not a URI; $uri must begin with 'urn:'.
     *
     * @param  string $uri
     * @return self
     */
    public function setUri($uri)
    {
        $this->validateUrn($uri);
        $this->uri = $uri;
        return $this;
    }

    /**
     * Retrieve URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set classmap
     *
     * @param  array $classmap
     * @return self
     * @throws Exception\InvalidArgumentException for any invalid class in the class map
     */
    public function setClassmap($classmap)
    {
        if (!is_array($classmap)) {
            throw new Exception\InvalidArgumentException('Classmap must be an array');
        }
        foreach ($classmap as $class) {
            if (!class_exists($class)) {
                throw new Exception\InvalidArgumentException('Invalid class in class map');
            }
        }

        $this->classmap = $classmap;
        return $this;
    }

    /**
     * Retrieve classmap
     *
     * @return mixed
     */
    public function getClassmap()
    {
        return $this->classmap;
    }

    /**
     * Set typemap with xml to php type mappings with appropriate validation.
     *
     * @param  array $typeMap
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setTypemap($typeMap)
    {
        if (!is_array($typeMap)) {
            throw new Exception\InvalidArgumentException('Typemap must be an array');
        }

        foreach ($typeMap as $type) {
            if (!is_callable($type['from_xml'])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid from_xml callback for type: %s',
                    $type['type_name']
                ));
            }
            if (!is_callable($type['to_xml'])) {
                throw new Exception\InvalidArgumentException('Invalid to_xml callback for type: ' . $type['type_name']);
            }
        }

        $this->typemap   = $typeMap;
        return $this;
    }

    /**
     * Retrieve typemap
     *
     * @return array
     */
    public function getTypemap()
    {
        return $this->typemap;
    }

    /**
     * Set wsdl
     *
     * @param  string $wsdl  URI or path to a WSDL
     * @return self
     */
    public function setWSDL($wsdl)
    {
        $this->wsdl = $wsdl;
        return $this;
    }

    /**
     * Retrieve wsdl
     *
     * @return string
     */
    public function getWSDL()
    {
        return $this->wsdl;
    }

    /**
     * Set the SOAP Feature options.
     *
     * @param  string|int $feature
     * @return self
     */
    public function setSoapFeatures($feature)
    {
        $this->features = $feature;
        return $this;
    }

    /**
     * Return current SOAP Features options
     *
     * @return int
     */
    public function getSoapFeatures()
    {
        return $this->features;
    }

    /**
     * Set the SOAP WSDL Caching Options
     *
     * @param  string|int|bool $options
     * @return self
     */
    public function setWSDLCache($options)
    {
        $this->wsdlCache = $options;
        return $this;
    }

    /**
     * Get current SOAP WSDL Caching option
     */
    public function getWSDLCache()
    {
        return $this->wsdlCache;
    }

    /**
     * Attach a function as a server method
     *
     * @param  array|string $function Function name, array of function names to attach,
     *             or SOAP_FUNCTIONS_ALL to attach all functions
     * @param  string $namespace Ignored
     * @return self
     * @throws Exception\InvalidArgumentException on invalid functions
     */
    public function addFunction($function, $namespace = '')
    {
        // Bail early if set to SOAP_FUNCTIONS_ALL
        if ($this->functions == SOAP_FUNCTIONS_ALL) {
            return $this;
        }

        if (is_array($function)) {
            foreach ($function as $func) {
                if (is_string($func) && function_exists($func)) {
                    $this->functions[] = $func;
                } else {
                    throw new Exception\InvalidArgumentException('One or more invalid functions specified in array');
                }
            }
        } elseif (is_string($function) && function_exists($function)) {
            $this->functions[] = $function;
        } elseif ($function == SOAP_FUNCTIONS_ALL) {
            $this->functions = SOAP_FUNCTIONS_ALL;
        } else {
            throw new Exception\InvalidArgumentException('Invalid function specified');
        }

        if (is_array($this->functions)) {
            $this->functions = array_unique($this->functions);
        }

        return $this;
    }

    /**
     * Attach a class to a server
     *
     * Accepts a class name to use when handling requests. Any additional
     * arguments will be passed to that class' constructor when instantiated.
     *
     * See {@link setObject()} to set pre-configured object instances as request handlers.
     *
     * @param  string|object $class Class name or object instance which executes
     *             SOAP Requests at endpoint.
     * @param  string $namespace
     * @param  null|array $argv
     * @return self
     * @throws Exception\InvalidArgumentException if called more than once, or if class does not exist
     */
    public function setClass($class, $namespace = '', $argv = null)
    {
        if (isset($this->class)) {
            throw new Exception\InvalidArgumentException(
                'A class has already been registered with this soap server instance'
            );
        }

        if (is_object($class)) {
            return $this->setObject($class);
        }

        if (!is_string($class)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid class argument (%s)',
                gettype($class)
            ));
        }

        if (!class_exists($class)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Class "%s" does not exist',
                $class
            ));
        }

        $this->class = $class;
        if (2 < func_num_args()) {
            $argv = func_get_args();
            $this->classArgs = array_slice($argv, 2);
        }

        return $this;
    }

    /**
     * Attach an object to a server
     *
     * Accepts an instantiated object to use when handling requests.
     *
     * @param  object $object
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setObject($object)
    {
        if (!is_object($object)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid object argument (%s)',
                gettype($object)
            ));
        }

        if (isset($this->object)) {
            throw new Exception\InvalidArgumentException(
                'An object has already been registered with this soap server instance'
            );
        }

        $this->object = $object;
        return $this;
    }

    /**
     * Return a server definition array
     *
     * Returns a list of all functions registered with {@link addFunction()},
     * merged with all public methods of the class set with {@link setClass()}
     * (if any).
     *
     * @return array
     */
    public function getFunctions()
    {
        $functions = array();
        if (null !== $this->class) {
            $functions = get_class_methods($this->class);
        } elseif (null !== $this->object) {
            $functions = get_class_methods($this->object);
        }

        return array_merge((array) $this->functions, $functions);
    }

    /**
     * Unimplemented: Load server definition
     *
     * @param  array $definition
     * @throws Exception\RuntimeException Unimplemented
     */
    public function loadFunctions($definition)
    {
        throw new Exception\RuntimeException('Unimplemented method.');
    }

    /**
     * Set server persistence
     *
     * @param  int $mode SOAP_PERSISTENCE_SESSION or SOAP_PERSISTENCE_REQUEST constants
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setPersistence($mode)
    {
        if (!in_array($mode, array(SOAP_PERSISTENCE_SESSION, SOAP_PERSISTENCE_REQUEST))) {
            throw new Exception\InvalidArgumentException('Invalid persistence mode specified');
        }

        $this->persistence = $mode;
        return $this;
    }

    /**
     * Get server persistence
     *
     * @return int
     */
    public function getPersistence()
    {
        return $this->persistence;
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
     * @param  DOMDocument|DOMNode|SimpleXMLElement|\stdClass|string $request
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    protected function _setRequest($request)
    {
        $xml = null;

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
            $xml = trim($xml);

            $loadEntities = libxml_disable_entity_loader(true);

            $dom = new DOMDocument();
            $loadStatus = $dom->loadXML($xml);

            libxml_disable_entity_loader($loadEntities);

            // @todo check libxml errors ? validate document ?
            if (strlen($xml) == 0 || !$loadStatus) {
                throw new Exception\InvalidArgumentException('Invalid XML');
            }

            foreach ($dom->childNodes as $child) {
                if ($child->nodeType === XML_DOCUMENT_TYPE_NODE) {
                    throw new Exception\InvalidArgumentException('Invalid XML: Detected use of illegal DOCTYPE');
                }
            }
        }

        $this->request = $xml;
        return $this;
    }

    /**
     * Retrieve request XML
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->request;
    }

    /**
     * Set return response flag
     *
     * If true, {@link handle()} will return the response instead of
     * automatically sending it back to the requesting client.
     *
     * The response is always available via {@link getResponse()}.
     *
     * @param  bool $flag
     * @return self
     */
    public function setReturnResponse($flag = true)
    {
        $this->returnResponse = (bool) $flag;
        return $this;
    }

    /**
     * Retrieve return response flag
     *
     * @return bool
     */
    public function getReturnResponse()
    {
        return $this->returnResponse;
    }

    /**
     * Get response XML
     *
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get SoapServer object
     *
     * Uses {@link $wsdl} and return value of {@link getOptions()} to instantiate
     * SoapServer object, and then registers any functions or class with it, as
     * well as persistence.
     *
     * @return SoapServer
     */
    public function getSoap()
    {
        if ($this->server instanceof SoapServer) {
            return $this->server;
        }

        $options = $this->getOptions();
        $server  = new SoapServer($this->wsdl, $options);

        if (!empty($this->functions)) {
            $server->addFunction($this->functions);
        }

        if (!empty($this->class)) {
            $args = $this->classArgs;
            array_unshift($args, $this->class);
            call_user_func_array(array($server, 'setClass'), $args);
        }

        if (!empty($this->object)) {
            $server->setObject($this->object);
        }

        if (null !== $this->persistence) {
            $server->setPersistence($this->persistence);
        }

        $this->server = $server;
        return $this->server;
    }

    /**
     * Proxy for _getSoap method
     * @see _getSoap
     * @return SoapServer the soapServer instance
    public function getSoap()
    {
        return $this->_getSoap();
    }
     */

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
     * cross-platform compatibility purposes).
     *
     * @param  DOMDocument|DOMNode|SimpleXMLElement|\stdClass|string $request Optional request
     * @return void|string
     */
    public function handle($request = null)
    {
        if (null === $request) {
            $request = file_get_contents('php://input');
        }

        // Set Server error handler
        $displayErrorsOriginalState = $this->_initializeSoapErrorContext();

        $setRequestException = null;
        try {
            $this->_setRequest($request);
        } catch (\Exception $e) {
            $setRequestException = $e;
        }

        $soap = $this->getSoap();

        $fault          = false;
        $this->response = '';

        if ($setRequestException instanceof \Exception) {
            // Create SOAP fault message if we've caught a request exception
            $fault = $this->fault($setRequestException->getMessage(), 'Sender');
        } else {
            ob_start();
            try {
                $soap->handle($this->request);
            } catch (\Exception $e) {
                $fault = $this->fault($e);
            }
            $this->response = ob_get_clean();
        }

        // Restore original error handler
        restore_error_handler();
        ini_set('display_errors', (string) $displayErrorsOriginalState);

        // Send a fault, if we have one
        if ($fault instanceof SoapFault && !$this->returnResponse) {
            $soap->fault($fault->faultcode, $fault->getMessage());

            return;
        }

        // Echo the response, if we're not returning it
        if (!$this->returnResponse) {
            echo $this->response;

            return;
        }

        // Return a fault, if we have it
        if ($fault instanceof SoapFault) {
            return $fault;
        }

        // Return the response
        return $this->response;
    }

    /**
     * Method initializes the error context that the SOAPServer environment will run in.
     *
     * @return bool display_errors original value
     */
    protected function _initializeSoapErrorContext()
    {
        $displayErrorsOriginalState = ini_get('display_errors');
        ini_set('display_errors', '0');
        set_error_handler(array($this, 'handlePhpErrors'), E_USER_ERROR);
        return $displayErrorsOriginalState;
    }

    /**
     * Set the debug mode.
     * In debug mode, all exceptions are send to the client.
     *
     * @param  bool $debug
     * @return self
     */
    public function setDebugMode($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Validate and register fault exception
     *
     * @param  string|array $class Exception class or array of exception classes
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function registerFaultException($class)
    {
        if (is_array($class)) {
            foreach ($class as $row) {
                $this->registerFaultException($row);
            }
        } elseif (is_string($class)
            && class_exists($class)
            && (is_subclass_of($class, 'Exception') || 'Exception' === $class)
        ) {
            $ref = new ReflectionClass($class);

            $this->faultExceptions[] = $ref->getName();
            $this->faultExceptions = array_unique($this->faultExceptions);
        } else {
            throw new Exception\InvalidArgumentException(
                'Argument for Zend\Soap\Server::registerFaultException should be'
                . ' string or array of strings with valid exception names'
            );
        }

        return $this;
    }

    /**
     * Checks if provided fault name is registered as valid in this server.
     *
     * @param string $fault Name of a fault class
     * @return bool
     */
    public function isRegisteredAsFaultException($fault)
    {
        if ($this->debug) {
            return true;
        }

        $ref        = new ReflectionClass($fault);
        $classNames = $ref->getName();
        return in_array($classNames, $this->faultExceptions);
    }

    /**
     * Deregister a fault exception from the fault exception stack
     *
     * @param  string $class
     * @return bool
     */
    public function deregisterFaultException($class)
    {
        if (in_array($class, $this->faultExceptions, true)) {
            $index = array_search($class, $this->faultExceptions);
            unset($this->faultExceptions[$index]);
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
        return $this->faultExceptions;
    }

    /**
     * Return caught exception during business code execution
     * @return null|\Exception caught exception
     */
    public function getException()
    {
        return $this->caughtException;
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
     * @param  string|\Exception $fault
     * @param  string $code SOAP Fault Codes
     * @return SoapFault
     */
    public function fault($fault = null, $code = 'Receiver')
    {
        $this->caughtException = (is_string($fault)) ? new \Exception($fault) : $fault;

        if ($fault instanceof \Exception) {
            if ($this->isRegisteredAsFaultException($fault)) {
                $message = $fault->getMessage();
                $eCode   = $fault->getCode();
                $code    = empty($eCode) ? $code : $eCode;
            } else {
                $message = 'Unknown error';
            }
        } elseif (is_string($fault)) {
            $message = $fault;
        } else {
            $message = 'Unknown error';
        }

        $allowedFaultModes = array(
            'VersionMismatch',
            'MustUnderstand',
            'DataEncodingUnknown',
            'Sender',
            'Receiver',
            'Server'
        );
        if (!in_array($code, $allowedFaultModes)) {
            $code = 'Receiver';
        }

        return new SoapFault($code, $message);
    }

    /**
     * Throw PHP errors as SoapFaults
     *
     * @param  int $errno
     * @param  string $errstr
     * @param  string $errfile
     * @param  int $errline
     * @param  array $errcontext
     * @throws SoapFault
     */
    public function handlePhpErrors($errno, $errstr, $errfile = null, $errline = null, array $errcontext = null)
    {
        throw $this->fault($errstr, 'Receiver');
    }
}
