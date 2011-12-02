<?php
/**
 * This file contains the code for the SOAP client.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 2.02 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is available at
 * through the world-wide-web at http://www.php.net/license/2_02.txt.  If you
 * did not receive a copy of the PHP license and are unable to obtain it
 * through the world-wide-web, please send a note to license@php.net so we can
 * mail you a copy immediately.
 *
 * @category   Web Services
 * @package    SOAP
 * @author     Dietrich Ayala <dietrich@ganx4.com> Original Author
 * @author     Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more
 * @author     Chuck Hagenbuch <chuck@horde.org>   Maintenance
 * @author     Jan Schneider <jan@horde.org>       Maintenance
 * @copyright  2003-2005 The PHP Group
 * @license    http://www.php.net/license/2_02.txt  PHP License 2.02
 * @link       http://pear.php.net/package/SOAP
 */

/** SOAP_Value */
require_once 'SOAP/Value.php';
require_once 'SOAP/Base.php';
require_once 'SOAP/Transport.php';
require_once 'SOAP/WSDL.php';
require_once 'SOAP/Fault.php';
require_once 'SOAP/Parser.php';

// Arnaud: the following code was taken from DataObject and adapted to suit

// this will be horrifically slow!!!!
// NOTE: Overload SEGFAULTS ON PHP4 + Zend Optimizer
// these two are BC/FC handlers for call in PHP4/5

/**
 * @package SOAP
 */
if (!class_exists('SOAP_Client_Overload', false)) {
    if (substr(zend_version(), 0, 1) > 1) {
        class SOAP_Client_Overload extends SOAP_Base {
            function __call($method, $args)
            {
                $return = null;
                $this->_call($method, $args, $return);
                return $return;
            }
        }
    } else {
        if (!function_exists('clone')) {
            eval('function clone($t) { return $t; }');
        }
        eval('
            class SOAP_Client_Overload extends SOAP_Base {
                function __call($method, $args, &$return)
                {
                    return $this->_call($method, $args, $return);
                }
            }');
    }
}

/**
 * SOAP Client Class
 *
 * This class is the main interface for making soap requests.
 *
 * basic usage:<code>
 *   $soapclient = new SOAP_Client( string path [ , boolean wsdl] );
 *   echo $soapclient->call( string methodname [ , array parameters] );
 * </code>
 * or, if using PHP 5+ or the overload extension:<code>
 *   $soapclient = new SOAP_Client( string path [ , boolean wsdl] );
 *   echo $soapclient->methodname( [ array parameters] );
 * </code>
 *
 * Originally based on SOAPx4 by Dietrich Ayala
 * http://dietrich.ganx4.com/soapx4
 *
 * @access   public
 * @package  SOAP
 * @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
 * @author   Stig Bakken <ssb@fast.no> Conversion to PEAR
 * @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class SOAP_Client extends SOAP_Client_Overload
{
    /**
     * Communication endpoint.
     *
     * Currently the following transport formats are supported:
     *  - HTTP
     *  - SMTP
     *
     * Example endpoints:
     *   http://www.example.com/soap/server.php
     *   https://www.example.com/soap/server.php
     *   mailto:soap@example.com
     *
     * @see SOAP_Client()
     * @var string
     */
    var $_endpoint = '';

    /**
     * The SOAP PORT name that is used by the client.
     *
     * @var string
     */
    var $_portName = '';

    /**
     * Endpoint type e.g. 'wdsl'.
     *
     * @var string
     */
    var $_endpointType = '';

    /**
     * The received xml.
     *
     * @var string
     */
    var $xml;

    /**
     * The outgoing and incoming data stream for debugging.
     *
     * @var string
     */
    var $wire;

    /**
     * The outgoing data stream for debugging.
     *
     * @var string
     */
    var $_last_request = null;

    /**
     * The incoming data stream for debugging.
     *
     * @var string
     */
    var $_last_response = null;

    /**
     * Options.
     *
     * @var array
     */
    var $_options = array('trace' => false);

    /**
     * The character encoding used for XML parser, etc.
     *
     * @var string
     */
    var $_encoding = SOAP_DEFAULT_ENCODING;

    /**
     * The array of SOAP_Headers that we are sending.
     *
     * @var array
     */
    var $headersOut = null;

    /**
     * The headers we recieved back in the response.
     *
     * @var array
     */
    var $headersIn = null;

    /**
     * Options for the HTTP_Request class (see HTTP/Request.php).
     *
     * @var array
     */
    var $_proxy_params = array();

    /**
     * The SOAP_Transport instance.
     *
     * @var SOAP_Transport
     */
    var $_soap_transport = null;

    /**
     * Constructor.
     *
     * @access public
     *
     * @param string $endpoint       An URL.
     * @param boolean $wsdl          Whether the endpoint is a WSDL file.
     * @param string $portName       The service's port name to use.
     * @param array $proxy_params    Options for the HTTP_Request class
     *                               @see HTTP_Request
     * @param boolean|string $cache  Use WSDL caching? The cache directory if
     *                               a string.
     */
    function SOAP_Client($endpoint, $wsdl = false, $portName = false,
                         $proxy_params = array(), $cache = false)
    {
        parent::SOAP_Base('Client');

        $this->_endpoint = $endpoint;
        $this->_portName = $portName;
        $this->_proxy_params = $proxy_params;

        // This hack should perhaps be removed as it might cause unexpected
        // behaviour.
        $wsdl = $wsdl
            ? $wsdl
            : strtolower(substr($endpoint, -4)) == 'wsdl';

        // make values
        if ($wsdl) {
            $this->_endpointType = 'wsdl';
            // instantiate wsdl class
            $this->_wsdl = new SOAP_WSDL($this->_endpoint,
                                         $this->_proxy_params,
                                         $cache);
            if ($this->_wsdl->fault) {
                $this->_raiseSoapFault($this->_wsdl->fault);
            }
        }
    }

    function _reset()
    {
        $this->xml = null;
        $this->wire = null;
        $this->_last_request = null;
        $this->_last_response = null;
        $this->headersIn = null;
        $this->headersOut = null;
    }

    /**
     * Sets the character encoding.
     *
     * Limited to 'UTF-8', 'US_ASCII' and 'ISO-8859-1'.
     *
     * @access public
     *
     * @param string encoding
     *
     * @return mixed  SOAP_Fault on error.
     */
    function setEncoding($encoding)
    {
        if (in_array($encoding, $this->_encodings)) {
            $this->_encoding = $encoding;
            return;
        }
        return $this->_raiseSoapFault('Invalid Encoding');
    }

    /**
     * Adds a header to the envelope.
     *
     * @access public
     *
     * @param SOAP_Header $soap_value  A SOAP_Header or an array with the
     *                                 elements 'name', 'namespace',
     *                                 'mustunderstand', and 'actor' to send
     *                                 as a header.
     */
    function addHeader($soap_value)
    {
        // Add a new header to the message.
        if (is_a($soap_value, 'SOAP_Header')) {
            $this->headersOut[] = $soap_value;
        } elseif (is_array($soap_value)) {
            // name, value, namespace, mustunderstand, actor
            $this->headersOut[] = new SOAP_Header($soap_value[0],
                                                  null,
                                                  $soap_value[1],
                                                  $soap_value[2],
                                                  $soap_value[3]);
        } else {
            $this->_raiseSoapFault('Invalid parameter provided to addHeader().  Must be an array or a SOAP_Header.');
        }
    }

    /**
     * Calls a method on the SOAP endpoint.
     *
     * The namespace parameter is overloaded to accept an array of options
     * that can contain data necessary for various transports if it is used as
     * an array, it MAY contain a namespace value and a soapaction value.  If
     * it is overloaded, the soapaction parameter is ignored and MUST be
     * placed in the options array.  This is done to provide backwards
     * compatibility with current clients, but may be removed in the future.
     * The currently supported values are:
     * - 'namespace'
     * - 'soapaction'
     * - 'timeout': HTTP socket timeout
     * - 'transfer-encoding': SMTP transport, Content-Transfer-Encoding: header
     * - 'from': SMTP transport, From: header
     * - 'subject': SMTP transport, Subject: header
     * - 'headers': SMTP transport, hash of extra SMTP headers
     * - 'attachments': what encoding to use for attachments (Mime, Dime)
     * - 'trace': whether to trace the SOAP communication
     * - 'style': 'document' or 'rpc'; when set to 'document' the parameters
     *   are not wrapped inside a tag with the SOAP action name
     * - 'use': 'literal' for literal encoding, anything else for section 5
     *   encoding; when set to 'literal' SOAP types will be omitted.
     * - 'keep_arrays_flat': use the tag name multiple times for each element
     *   when passing in an array in literal mode
     * - 'no_type_prefix': supress adding of the namespace prefix
     *
     * @access public
     *
     * @param string $method           The method to call.
     * @param array $params            The method parameters.
     * @param string|array $namespace  Namespace or hash with options. Note:
     *                                 most options need to be repeated for
     *                                 SOAP_Value instances.
     * @param string $soapAction
     *
     * @return mixed  The method result or a SOAP_Fault on error.
     */
    function call($method, $params, $namespace = false, $soapAction = false)
    {
        $this->headersIn = null;
        $this->_last_request = null;
        $this->_last_response = null;
        $this->wire = null;
        $this->xml = null;

        $soap_data = $this->_generate($method, $params, $namespace, $soapAction);
        if (PEAR::isError($soap_data)) {
            $fault = $this->_raiseSoapFault($soap_data);
            return $fault;
        }

        // _generate() may have changed the endpoint if the WSDL has more
        // than one service, so we need to see if we need to generate a new
        // transport to hook to a different URI.  Since the transport protocol
        // can also change, we need to get an entirely new object.  This could
        // probably be optimized.
        if (!$this->_soap_transport ||
            $this->_endpoint != $this->_soap_transport->url) {
            $this->_soap_transport = SOAP_Transport::getTransport($this->_endpoint);
            if (PEAR::isError($this->_soap_transport)) {
                $fault = $this->_raiseSoapFault($this->_soap_transport);
                $this->_soap_transport = null;
                return $fault;
            }
        }
        $this->_soap_transport->encoding = $this->_encoding;

        // Send the message.
        $transport_options = array_merge_recursive($this->_proxy_params,
                                                   $this->_options);
        $this->xml = $this->_soap_transport->send($soap_data, $transport_options);

        // Save the wire information for debugging.
        if ($this->_options['trace']) {
            $this->_last_request = $this->_soap_transport->outgoing_payload;
            $this->_last_response = $this->_soap_transport->incoming_payload;
            $this->wire = $this->getWire();
        }
        if ($this->_soap_transport->fault) {
            $fault = $this->_raiseSoapFault($this->xml);
            return $fault;
        }

        if (isset($this->_options['result']) &&
            $this->_options['result'] != 'parse') {
            return $this->xml;
        }

        $this->__result_encoding = $this->_soap_transport->result_encoding;

        $result = $this->parseResponse($this->xml, $this->__result_encoding,
                                       $this->_soap_transport->attachments);
        return $result;
    }

    /**
     * Sets an option to use with the transport layers.
     *
     * For example:
     * <code>
     * $soapclient->setOpt('curl', CURLOPT_VERBOSE, 1)
     * </code>
     * to pass a specific option to curl if using an SSL connection.
     *
     * @access public
     *
     * @param string $category  Category to which the option applies or option
     *                          name.
     * @param string $option    An option name if $category is a category name,
     *                          an option value if $category is an option name.
     * @param string $value     An option value if $category is a category
     *                          name.
     */
    function setOpt($category, $option, $value = null)
    {
        if (!is_null($value)) {
            if (!isset($this->_options[$category])) {
                $this->_options[$category] = array();
            }
            $this->_options[$category][$option] = $value;
        } else {
            $this->_options[$category] = $option;
        }
    }

    /**
     * Call method supporting the overload extension.
     *
     * If the overload extension is loaded, you can call the client class with
     * a soap method name:
     * <code>
     * $soap = new SOAP_Client(....);
     * $value = $soap->getStockQuote('MSFT');
     * </code>
     *
     * @access public
     *
     * @param string $method        The method to call.
     * @param array $params         The method parameters.
     * @param mixed $return_value   Will get the method's return value
     *                              assigned.
     *
     * @return boolean  Always true.
     */
    function _call($method, $params, &$return_value)
    {
        // Overloading lowercases the method name, we need to look into the
        // WSDL and try to find the correct method name to get the correct
        // case for the call.
        if ($this->_wsdl) {
            $this->_wsdl->matchMethod($method);
        }

        $return_value = $this->call($method, $params);

        return true;
    }

    /**
     * Returns the XML content of the last SOAP request.
     *
     * @return string  The last request.
     */
    function getLastRequest()
    {
        return $this->_last_request;
    }

    /**
     * Returns the XML content of the last SOAP response.
     *
     * @return string  The last response.
     */
    function getLastResponse()
    {
        return $this->_last_response;
    }

    /**
     * Sets the SOAP encoding.
     *
     * The default encoding is section 5 encoded.
     *
     * @param string $use  Either 'literal' or 'encoded' (section 5).
     */
    function setUse($use)
    {
        $this->_options['use'] = $use;
    }

    /**
     * Sets the SOAP encoding style.
     *
     * The default style is rpc.
     *
     * @param string $style  Either 'document' or 'rpc'.
     */
    function setStyle($style)
    {
        $this->_options['style'] = $style;
    }

    /**
     * Sets whether to trace the traffic on the transport level.
     *
     * @see getWire()
     *
     * @param boolean $trace
     */
    function setTrace($trace)
    {
        $this->_options['trace'] = $trace;
    }

    /**
     * Generates the complete XML SOAP message for an RPC call.
     *
     * @see call()
     *
     * @param string $method           The method to call.
     * @param array $params            The method parameters.
     * @param string|array $namespace  Namespace or hash with options. Note:
     *                                 most options need to be repeated for
     *                                 SOAP_Value instances.
     * @param string $soapAction
     *
     * @return string  The SOAP message including envelope.
     */
    function _generate($method, $params, $namespace = false,
                       $soapAction = false)
    {
        $this->fault = null;
        $this->_options['input'] = 'parse';
        $this->_options['result'] = 'parse';
        $this->_options['parameters'] = false;

        if ($params && !is_array($params)) {
            $params = array($params);
        }

        if (is_array($namespace)) {
            // Options passed as a hash.
            foreach ($namespace as $optname => $opt) {
                $this->_options[strtolower($optname)] = $opt;
            }
        } else {
            // We'll place $soapAction into our array for usage in the
            // transport.
            if ($soapAction) {
                $this->_options['soapaction'] = $soapAction;
            }
            if ($namespace) {
                $this->_options['namespace'] = $namespace;
            }
        }
        if (isset($this->_options['namespace'])) {
            $namespace = $this->_options['namespace'];
        } else {
            $namespace = false;
        }

        if ($this->_endpointType == 'wsdl') {
            $this->_setSchemaVersion($this->_wsdl->xsd);

            // Get port name.
            if (!$this->_portName) {
                $this->_portName = $this->_wsdl->getPortName($method);
            }
            if (PEAR::isError($this->_portName)) {
                return $this->_raiseSoapFault($this->_portName);
            }

            // Get endpoint.
            $this->_endpoint = $this->_wsdl->getEndpoint($this->_portName);
            if (PEAR::isError($this->_endpoint)) {
                return $this->_raiseSoapFault($this->_endpoint);
            }

            // Get operation data.
            $opData = $this->_wsdl->getOperationData($this->_portName, $method);

            if (PEAR::isError($opData)) {
                return $this->_raiseSoapFault($opData);
            }
            $namespace                    = isset($opData['namespace'])?$opData['namespace']:'';
            $this->_options['style']      = $opData['style'];
            $this->_options['use']        = $opData['input']['use'];
            $this->_options['soapaction'] = $opData['soapAction'];

            // Set input parameters.
            if ($this->_options['input'] == 'parse') {
                $this->_options['parameters'] = $opData['parameters'];
                $nparams = array();
                if (isset($opData['input']['parts']) &&
                    count($opData['input']['parts'])) {
                    foreach ($opData['input']['parts'] as $name => $part) {
                        $xmlns = '';
                        $attrs = array();
                        // Is the name a complex type?
                        if (isset($part['element'])) {
                            $xmlns = $this->_wsdl->namespaces[$part['namespace']];
                            $part = $this->_wsdl->elements[$part['namespace']][$part['type']];
                            $name = $part['name'];
                        }
                        if (isset($params[$name]) ||
                            $this->_wsdl->getDataHandler($name, $part['namespace'])) {
                            $nparams[$name] =& $params[$name];
                        } else {
                            // We now force an associative array for
                            // parameters if using WSDL.
                            return $this->_raiseSoapFault("The named parameter $name is not in the call parameters.");
                        }
                        if (gettype($nparams[$name]) != 'object' ||
                            !$nparams[$name] instanceof SOAP_Value) {
                            // Type is likely a qname, split it apart, and get
                            // the type namespace from WSDL.
                            $qname = new QName($part['type']);
                            if ($qname->ns) {
                                $type_namespace = $this->_wsdl->namespaces[$qname->ns];
                            } elseif (isset($part['namespace'])) {
                                $type_namespace = $this->_wsdl->namespaces[$part['namespace']];
                            } else {
                                $type_namespace = null;
                            }
                            $qname->namespace = $type_namespace;
                            $pqname = $name;
                            if ($xmlns) {
                                $pqname = '{' . $xmlns . '}' . $name;
                            }
                            $nparams[$name] = new SOAP_Value($pqname,
                                                              $qname->fqn(),
                                                              $nparams[$name],
                                                              $attrs);
                        } else {
                            // WSDL fixups to the SOAP value.
                        }
                    }
                }
                $params =& $nparams;
                unset($nparams);
            }
        } else {
            $this->_setSchemaVersion(SOAP_XML_SCHEMA_VERSION);
        }

        // Serialize the message.
        $this->_section5 = (!isset($this->_options['use']) ||
                            $this->_options['use'] != 'literal');

        if (!isset($this->_options['style']) ||
            $this->_options['style'] == 'rpc') {
            $this->_options['style'] = 'rpc';
            $this->docparams = true;
            $mqname = new QName($method, $namespace);
            $methodValue = new SOAP_Value($mqname->fqn(), 'Struct', $params,
                                          array(), $this->_options);
            $soap_msg = $this->makeEnvelope($methodValue,
                                            $this->headersOut,
                                            $this->_encoding,
                                            $this->_options);
        } else {
            if (!$params) {
                $mqname = new QName($method, $namespace);
                $params = new SOAP_Value($mqname->fqn(), 'Struct', null);
            } elseif ($this->_options['input'] == 'parse') {
                if (is_array($params)) {
                    $nparams = array();
                    $keys = array_keys($params);
                    foreach ($keys as $k) {
                        if (gettype($params[$k]) != 'object') {
                            $nparams[] = new SOAP_Value($k,
                                                        false,
                                                        $params[$k]);
                        } else {
                            $nparams[] =& $params[$k];
                        }
                    }
                    $params =& $nparams;
                }
                if ($this->_options['parameters']) {
                    $mqname = new QName($method, $namespace);
                    $params = new SOAP_Value($mqname->fqn(),
                                             'Struct',
                                             $params);
                }
            }
            $soap_msg = $this->makeEnvelope($params,
                                            $this->headersOut,
                                            $this->_encoding,
                                            $this->_options);
        }
        $this->headersOut = null;

        if (PEAR::isError($soap_msg)) {
            return $this->_raiseSoapFault($soap_msg);
        }

        // Handle MIME or DIME encoding.
        // TODO: DIME encoding should move to the transport, do it here for
        // now and for ease of getting it done.
        if (count($this->_attachments)) {
            if ((isset($this->_options['attachments']) &&
                 $this->_options['attachments'] == 'Mime') ||
                isset($this->_options['Mime'])) {
                $soap_msg = $this->_makeMimeMessage($soap_msg, $this->_encoding);
            } else {
                // default is dime
                $soap_msg = $this->_makeDIMEMessage($soap_msg, $this->_encoding);
                $this->_options['headers']['Content-Type'] = 'application/dime';
            }
            if (PEAR::isError($soap_msg)) {
                return $this->_raiseSoapFault($soap_msg);
            }
        }

        // Instantiate client.
        if (is_array($soap_msg)) {
            $soap_data = $soap_msg['body'];
            if (count($soap_msg['headers'])) {
                if (isset($this->_options['headers'])) {
                    $this->_options['headers'] = array_merge($this->_options['headers'], $soap_msg['headers']);
                } else {
                    $this->_options['headers'] = $soap_msg['headers'];
                }
            }
        } else {
            $soap_data = $soap_msg;
        }

        return $soap_data;
    }

    /**
     * Parses a SOAP response.
     *
     * @see SOAP_Parser::
     *
     * @param string $response    XML content of SOAP response.
     * @param string $encoding    Character set encoding, defaults to 'UTF-8'.
     * @param array $attachments  List of attachments.
     */
    function parseResponse($response, $encoding, $attachments)
    {
        // Parse the response.
        $response = new SOAP_Parser($response, $encoding, $attachments);
        if ($response->fault) {
            $fault = $this->_raiseSoapFault($response->fault);
            return $fault;
        }

        // Return array of parameters.
        $return = $response->getResponse();
        $headers = $response->getHeaders();
        if ($headers) {
            $this->headersIn = $this->_decodeResponse($headers, false);
        }

        $decoded = $this->_decodeResponse($return);
        return $decoded;
    }

    /**
     * Converts a complex SOAP_Value into a PHP Array
     *
     * @param SOAP_Value $response  Value object.
     * @param boolean $shift
     *
     * @return array
     */
    function _decodeResponse($response, $shift = true)
    {
        if (!$response) {
            $decoded = null;
            return $decoded;
        }

        // Check for valid response.
        if (PEAR::isError($response)) {
            $fault = $this->_raiseSoapFault($response);
            return $fault;
        } elseif (!$response instanceof SOAP_Value) {
            $fault = $this->_raiseSoapFault("Didn't get SOAP_Value object back from client");
            return $fault;
        }

        // Decode to native php datatype.
        $returnArray = $this->_decode($response);

        // Fault?
        if (PEAR::isError($returnArray)) {
            $fault = $this->_raiseSoapFault($returnArray);
            return $fault;
        }

        if (is_object($returnArray) &&
            strcasecmp(get_class($returnArray), 'stdClass') == 0) {
            $returnArray = get_object_vars($returnArray);
        }

        if (is_array($returnArray)) {
            if (isset($returnArray['faultcode']) ||
                isset($returnArray[SOAP_BASE::SOAPENVPrefix().':faultcode'])) {
                $faultcode = $faultstring = $faultdetail = $faultactor = '';
                foreach ($returnArray as $k => $v) {
                    if (stristr($k, 'faultcode')) $faultcode = $v;
                    if (stristr($k, 'faultstring')) $faultstring = $v;
                    if (stristr($k, 'detail')) $faultdetail = $v;
                    if (stristr($k, 'faultactor')) $faultactor = $v;
                }
                $fault = $this->_raiseSoapFault($faultstring, $faultdetail,
                                                $faultactor, $faultcode);
                return $fault;
            }
            // Return array of return values.
            if ($shift && count($returnArray) == 1) {
                $decoded = array_shift($returnArray);
                return $decoded;
            }
            return $returnArray;
        }

        return $returnArray;
    }

    /**
     * Returns the outgoing and incoming traffic on the transport level.
     *
     * Tracing has to be enabled.
     *
     * @see setTrace()
     *
     * @return string  The complete traffic between the client and the server.
     */
    function getWire()
    {
        if ($this->_options['trace'] &&
            ($this->_last_request || $this->_last_response)) {
            return "OUTGOING:\n\n" .
                $this->_last_request .
                "\n\nINCOMING\n\n" .
                preg_replace("/></",">\r\n<", $this->_last_response);
        }

        return null;
    }

}
