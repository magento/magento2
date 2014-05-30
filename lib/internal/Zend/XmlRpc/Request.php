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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_XmlRpc_Value
 */
#require_once 'Zend/XmlRpc/Value.php';

/**
 * Zend_XmlRpc_Fault
 */
#require_once 'Zend/XmlRpc/Fault.php';

/**
 * XmlRpc Request object
 *
 * Encapsulates an XmlRpc request, holding the method call and all parameters.
 * Provides accessors for these, as well as the ability to load from XML and to
 * create the XML request string.
 *
 * Additionally, if errors occur setting the method or parsing XML, a fault is
 * generated and stored in {@link $_fault}; developers may check for it using
 * {@link isFault()} and {@link getFault()}.
 *
 * @category Zend
 * @package  Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version $Id: Request.php 20208 2010-01-11 22:37:37Z lars $
 */
class Zend_XmlRpc_Request
{
    /**
     * Request character encoding
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Method to call
     * @var string
     */
    protected $_method;

    /**
     * XML request
     * @var string
     */
    protected $_xml;

    /**
     * Method parameters
     * @var array
     */
    protected $_params = array();

    /**
     * Fault object, if any
     * @var Zend_XmlRpc_Fault
     */
    protected $_fault = null;

    /**
     * XML-RPC type for each param
     * @var array
     */
    protected $_types = array();

    /**
     * XML-RPC request params
     * @var array
     */
    protected $_xmlRpcParams = array();

    /**
     * Create a new XML-RPC request
     *
     * @param string $method (optional)
     * @param array $params  (optional)
     */
    public function __construct($method = null, $params = null)
    {
        if ($method !== null) {
            $this->setMethod($method);
        }

        if ($params !== null) {
            $this->setParams($params);
        }
    }


    /**
     * Set encoding to use in request
     *
     * @param string $encoding
     * @return Zend_XmlRpc_Request
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        Zend_XmlRpc_Value::setEncoding($encoding);
        return $this;
    }

    /**
     * Retrieve current request encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set method to call
     *
     * @param string $method
     * @return boolean Returns true on success, false if method name is invalid
     */
    public function setMethod($method)
    {
        if (!is_string($method) || !preg_match('/^[a-z0-9_.:\/]+$/i', $method)) {
            $this->_fault = new Zend_XmlRpc_Fault(634, 'Invalid method name ("' . $method . '")');
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->_method = $method;
        return true;
    }

    /**
     * Retrieve call method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Add a parameter to the parameter stack
     *
     * Adds a parameter to the parameter stack, associating it with the type
     * $type if provided
     *
     * @param mixed $value
     * @param string $type Optional; type hinting
     * @return void
     */
    public function addParam($value, $type = null)
    {
        $this->_params[] = $value;
        if (null === $type) {
            // Detect type if not provided explicitly
            if ($value instanceof Zend_XmlRpc_Value) {
                $type = $value->getType();
            } else {
                $xmlRpcValue = Zend_XmlRpc_Value::getXmlRpcValue($value);
                $type        = $xmlRpcValue->getType();
            }
        }
        $this->_types[]  = $type;
        $this->_xmlRpcParams[] = array('value' => $value, 'type' => $type);
    }

    /**
     * Set the parameters array
     *
     * If called with a single, array value, that array is used to set the
     * parameters stack. If called with multiple values or a single non-array
     * value, the arguments are used to set the parameters stack.
     *
     * Best is to call with array of the format, in order to allow type hinting
     * when creating the XMLRPC values for each parameter:
     * <code>
     * $array = array(
     *     array(
     *         'value' => $value,
     *         'type'  => $type
     *     )[, ... ]
     * );
     * </code>
     *
     * @access public
     * @return void
     */
    public function setParams()
    {
        $argc = func_num_args();
        $argv = func_get_args();
        if (0 == $argc) {
            return;
        }

        if ((1 == $argc) && is_array($argv[0])) {
            $params     = array();
            $types      = array();
            $wellFormed = true;
            foreach ($argv[0] as $arg) {
                if (!is_array($arg) || !isset($arg['value'])) {
                    $wellFormed = false;
                    break;
                }
                $params[] = $arg['value'];

                if (!isset($arg['type'])) {
                    $xmlRpcValue = Zend_XmlRpc_Value::getXmlRpcValue($arg['value']);
                    $arg['type'] = $xmlRpcValue->getType();
                }
                $types[] = $arg['type'];
            }
            if ($wellFormed) {
                $this->_xmlRpcParams = $argv[0];
                $this->_params = $params;
                $this->_types  = $types;
            } else {
                $this->_params = $argv[0];
                $this->_types  = array();
                $xmlRpcParams  = array();
                foreach ($argv[0] as $arg) {
                    if ($arg instanceof Zend_XmlRpc_Value) {
                        $type = $arg->getType();
                    } else {
                        $xmlRpcValue = Zend_XmlRpc_Value::getXmlRpcValue($arg);
                        $type        = $xmlRpcValue->getType();
                    }
                    $xmlRpcParams[] = array('value' => $arg, 'type' => $type);
                    $this->_types[] = $type;
                }
                $this->_xmlRpcParams = $xmlRpcParams;
            }
            return;
        }

        $this->_params = $argv;
        $this->_types  = array();
        $xmlRpcParams  = array();
        foreach ($argv as $arg) {
            if ($arg instanceof Zend_XmlRpc_Value) {
                $type = $arg->getType();
            } else {
                $xmlRpcValue = Zend_XmlRpc_Value::getXmlRpcValue($arg);
                $type        = $xmlRpcValue->getType();
            }
            $xmlRpcParams[] = array('value' => $arg, 'type' => $type);
            $this->_types[] = $type;
        }
        $this->_xmlRpcParams = $xmlRpcParams;
    }

    /**
     * Retrieve the array of parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Return parameter types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * Load XML and parse into request components
     *
     * @param string $request
     * @return boolean True on success, false if an error occurred.
     */
    public function loadXml($request)
    {
        if (!is_string($request)) {
            $this->_fault = new Zend_XmlRpc_Fault(635);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        try {
            $xml = new SimpleXMLElement($request);
        } catch (Exception $e) {
            // Not valid XML
            $this->_fault = new Zend_XmlRpc_Fault(631);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        // Check for method name
        if (empty($xml->methodName)) {
            // Missing method name
            $this->_fault = new Zend_XmlRpc_Fault(632);
            $this->_fault->setEncoding($this->getEncoding());
            return false;
        }

        $this->_method = (string) $xml->methodName;

        // Check for parameters
        if (!empty($xml->params)) {
            $types = array();
            $argv  = array();
            foreach ($xml->params->children() as $param) {
                if (!isset($param->value)) {
                    $this->_fault = new Zend_XmlRpc_Fault(633);
                    $this->_fault->setEncoding($this->getEncoding());
                    return false;
                }

                try {
                    $param   = Zend_XmlRpc_Value::getXmlRpcValue($param->value, Zend_XmlRpc_Value::XML_STRING);
                    $types[] = $param->getType();
                    $argv[]  = $param->getValue();
                } catch (Exception $e) {
                    $this->_fault = new Zend_XmlRpc_Fault(636);
                    $this->_fault->setEncoding($this->getEncoding());
                    return false;
                }
            }

            $this->_types  = $types;
            $this->_params = $argv;
        }

        $this->_xml = $request;

        return true;
    }

    /**
     * Does the current request contain errors and should it return a fault
     * response?
     *
     * @return boolean
     */
    public function isFault()
    {
        return $this->_fault instanceof Zend_XmlRpc_Fault;
    }

    /**
     * Retrieve the fault response, if any
     *
     * @return null|Zend_XmlRpc_Fault
     */
    public function getFault()
    {
        return $this->_fault;
    }

    /**
     * Retrieve method parameters as XMLRPC values
     *
     * @return array
     */
    protected function _getXmlRpcParams()
    {
        $params = array();
        if (is_array($this->_xmlRpcParams)) {
            foreach ($this->_xmlRpcParams as $param) {
                $value = $param['value'];
                $type  = isset($param['type']) ? $param['type'] : Zend_XmlRpc_Value::AUTO_DETECT_TYPE;

                if (!$value instanceof Zend_XmlRpc_Value) {
                    $value = Zend_XmlRpc_Value::getXmlRpcValue($value, $type);
                }
                $params[] = $value;
            }
        }

        return $params;
    }

    /**
     * Create XML request
     *
     * @return string
     */
    public function saveXml()
    {
        $args   = $this->_getXmlRpcParams();
        $method = $this->getMethod();

        $generator = Zend_XmlRpc_Value::getGenerator();
        $generator->openElement('methodCall')
                  ->openElement('methodName', $method)
                  ->closeElement('methodName');

        if (is_array($args) && count($args)) {
            $generator->openElement('params');

            foreach ($args as $arg) {
                $generator->openElement('param');
                $arg->generateXml();
                $generator->closeElement('param');
            }
            $generator->closeElement('params');
        }
        $generator->closeElement('methodCall');

        return $generator->flush();
    }

    /**
     * Return XML request
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }
}
