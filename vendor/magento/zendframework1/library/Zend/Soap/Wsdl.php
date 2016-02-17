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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Soap_Wsdl_Strategy_Interface
 */
#require_once "Zend/Soap/Wsdl/Strategy/Interface.php";

/**
 * @see Zend_Soap_Wsdl_Strategy_Abstract
 */
#require_once "Zend/Soap/Wsdl/Strategy/Abstract.php";

/** @see Zend_Xml_Security */
#require_once "Zend/Xml/Security.php";

/**
 * Zend_Soap_Wsdl
 *
 * @category   Zend
 * @package    Zend_Soap
 */
class Zend_Soap_Wsdl
{
    /**
     * @var object DomDocument Instance
     */
    private $_dom;

    /**
     * @var object WSDL Root XML_Tree_Node
     */
    private $_wsdl;

    /**
     * @var string URI where the WSDL will be available
     */
    private $_uri;

    /**
     * @var DOMElement
     */
    private $_schema = null;

    /**
     * Types defined on schema
     *
     * @var array
     */
    private $_includedTypes = array();

    /**
     * Strategy for detection of complex types
     */
    protected $_strategy = null;


    /**
     * Constructor
     *
     * @param string  $name Name of the Web Service being Described
     * @param string  $uri URI where the WSDL will be available
     * @param boolean|string|Zend_Soap_Wsdl_Strategy_Interface $strategy
     */
    public function __construct($name, $uri, $strategy = true)
    {
        if ($uri instanceof Zend_Uri_Http) {
            $uri = $uri->getUri();
        }
        $this->_uri = $uri;

        /**
         * @todo change DomDocument object creation from cparsing to construxting using API
         * It also should authomatically escape $name and $uri values if necessary
         */
        $wsdl = "<?xml version='1.0' ?>
                <definitions name='$name' targetNamespace='$uri'
                    xmlns='http://schemas.xmlsoap.org/wsdl/'
                    xmlns:tns='$uri'
                    xmlns:soap='http://schemas.xmlsoap.org/wsdl/soap/'
                    xmlns:xsd='http://www.w3.org/2001/XMLSchema'
                    xmlns:soap-enc='http://schemas.xmlsoap.org/soap/encoding/'
                    xmlns:wsdl='http://schemas.xmlsoap.org/wsdl/'></definitions>";
        $this->_dom = new DOMDocument();
        if (!$this->_dom = Zend_Xml_Security::scan($wsdl, $this->_dom)) {
            #require_once 'Zend/Server/Exception.php';
            throw new Zend_Server_Exception('Unable to create DomDocument');
        }
        $this->_wsdl = $this->_dom->documentElement;

        $this->setComplexTypeStrategy($strategy);
    }

    /**
     * Set a new uri for this WSDL
     *
     * @param  string|Zend_Uri_Http $uri
     * @return Zend_Server_Wsdl
     */
    public function setUri($uri)
    {
        if ($uri instanceof Zend_Uri_Http) {
            $uri = $uri->getUri();
        }
        $oldUri = $this->_uri;
        $this->_uri = $uri;

        if($this->_dom !== null) {
            // @todo: This is the worst hack ever, but its needed due to design and non BC issues of WSDL generation
            $xml = $this->_dom->saveXML();
            $xml = str_replace($oldUri, $uri, $xml);
            $this->_dom = new DOMDocument();
            $this->_dom = Zend_Xml_Security::scan($xml, $this->_dom);
        }

        return $this;
    }

    /**
     * Set a strategy for complex type detection and handling
     *
     * @todo Boolean is for backwards compability with extractComplexType object var. Remove it in later versions.
     * @param boolean|string|Zend_Soap_Wsdl_Strategy_Interface $strategy
     * @return Zend_Soap_Wsdl
     */
    public function setComplexTypeStrategy($strategy)
    {
        if($strategy === true) {
            #require_once "Zend/Soap/Wsdl/Strategy/DefaultComplexType.php";
            $strategy = new Zend_Soap_Wsdl_Strategy_DefaultComplexType();
        } else if($strategy === false) {
            #require_once "Zend/Soap/Wsdl/Strategy/AnyType.php";
            $strategy = new Zend_Soap_Wsdl_Strategy_AnyType();
        } else if(is_string($strategy)) {
            if(class_exists($strategy)) {
                $strategy = new $strategy();
            } else {
                #require_once "Zend/Soap/Wsdl/Exception.php";
                throw new Zend_Soap_Wsdl_Exception(
                    sprintf("Strategy with name '%s does not exist.", $strategy
                ));
            }
        }

        if(!($strategy instanceof Zend_Soap_Wsdl_Strategy_Interface)) {
            #require_once "Zend/Soap/Wsdl/Exception.php";
            throw new Zend_Soap_Wsdl_Exception("Set a strategy that is not of type 'Zend_Soap_Wsdl_Strategy_Interface'");
        }
        $this->_strategy = $strategy;
        return $this;
    }

    /**
     * Get the current complex type strategy
     *
     * @return Zend_Soap_Wsdl_Strategy_Interface
     */
    public function getComplexTypeStrategy()
    {
        return $this->_strategy;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_messages message} element to the WSDL
     *
     * @param string $name Name for the {@link http://www.w3.org/TR/wsdl#_messages message}
     * @param array $parts An array of {@link http://www.w3.org/TR/wsdl#_message parts}
     *                     The array is constructed like: 'name of part' => 'part xml schema data type'
     *                     or 'name of part' => array('type' => 'part xml schema type')
     *                     or 'name of part' => array('element' => 'part xml element name')
     * @return object The new message's XML_Tree_Node for use in {@link function addDocumentation}
     */
    public function addMessage($name, $parts)
    {
        $message = $this->_dom->createElement('message');

        $message->setAttribute('name', $name);

        if (sizeof($parts) > 0) {
            foreach ($parts as $name => $type) {
                $part = $this->_dom->createElement('part');
                $part->setAttribute('name', $name);
                if (is_array($type)) {
                    foreach ($type as $key => $value) {
                        $part->setAttribute($key, $value);
                    }
                } else {
                    $part->setAttribute('type', $type);
                }
                $message->appendChild($part);
            }
        }

        $this->_wsdl->appendChild($message);

        return $message;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_porttypes portType} element to the WSDL
     *
     * @param string $name portType element's name
     * @return object The new portType's XML_Tree_Node for use in {@link function addPortOperation} and {@link function addDocumentation}
     */
    public function addPortType($name)
    {
        $portType = $this->_dom->createElement('portType');
        $portType->setAttribute('name', $name);
        $this->_wsdl->appendChild($portType);

        return $portType;
    }

    /**
     * Add an {@link http://www.w3.org/TR/wsdl#_request-response operation} element to a portType element
     *
     * @param object $portType a portType XML_Tree_Node, from {@link function addPortType}
     * @param string $name Operation name
     * @param string $input Input Message
     * @param string $output Output Message
     * @param string $fault Fault Message
     * @return object The new operation's XML_Tree_Node for use in {@link function addDocumentation}
     */
    public function addPortOperation($portType, $name, $input = false, $output = false, $fault = false)
    {
        $operation = $this->_dom->createElement('operation');
        $operation->setAttribute('name', $name);

        if (is_string($input) && (strlen(trim($input)) >= 1)) {
            $node = $this->_dom->createElement('input');
            $node->setAttribute('message', $input);
            $operation->appendChild($node);
        }
        if (is_string($output) && (strlen(trim($output)) >= 1)) {
            $node= $this->_dom->createElement('output');
            $node->setAttribute('message', $output);
            $operation->appendChild($node);
        }
        if (is_string($fault) && (strlen(trim($fault)) >= 1)) {
            $node = $this->_dom->createElement('fault');
            $node->setAttribute('message', $fault);
            $operation->appendChild($node);
        }

        $portType->appendChild($operation);

        return $operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_bindings binding} element to WSDL
     *
     * @param string $name Name of the Binding
     * @param string $type name of the portType to bind
     * @return object The new binding's XML_Tree_Node for use with {@link function addBindingOperation} and {@link function addDocumentation}
     */
    public function addBinding($name, $portType)
    {
        $binding = $this->_dom->createElement('binding');
        $binding->setAttribute('name', $name);
        $binding->setAttribute('type', $portType);

        $this->_wsdl->appendChild($binding);

        return $binding;
    }

    /**
     * Add an operation to a binding element
     *
     * @param object $binding A binding XML_Tree_Node returned by {@link function addBinding}
     * @param array $input An array of attributes for the input element, allowed keys are: 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param array $output An array of attributes for the output element, allowed keys are: 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param array $fault An array of attributes for the fault element, allowed keys are: 'name', 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @return object The new Operation's XML_Tree_Node for use with {@link function addSoapOperation} and {@link function addDocumentation}
     */
    public function addBindingOperation($binding, $name, $input = false, $output = false, $fault = false)
    {
        $operation = $this->_dom->createElement('operation');
        $operation->setAttribute('name', $name);

        if (is_array($input)) {
            $node = $this->_dom->createElement('input');
            $soap_node = $this->_dom->createElement('soap:body');
            foreach ($input as $name => $value) {
                $soap_node->setAttribute($name, $value);
            }
            $node->appendChild($soap_node);
            $operation->appendChild($node);
        }

        if (is_array($output)) {
            $node = $this->_dom->createElement('output');
            $soap_node = $this->_dom->createElement('soap:body');
            foreach ($output as $name => $value) {
                $soap_node->setAttribute($name, $value);
            }
            $node->appendChild($soap_node);
            $operation->appendChild($node);
        }

        if (is_array($fault)) {
            $node = $this->_dom->createElement('fault');
            /**
             * Note. Do we really need name attribute to be also set at wsdl:fault node???
             * W3C standard doesn't mention it (http://www.w3.org/TR/wsdl#_soap:fault)
             * But some real world WSDLs use it, so it may be required for compatibility reasons.
             */
            if (isset($fault['name'])) {
                $node->setAttribute('name', $fault['name']);
            }

            $soap_node = $this->_dom->createElement('soap:fault');
            foreach ($fault as $name => $value) {
                $soap_node->setAttribute($name, $value);
            }
            $node->appendChild($soap_node);
            $operation->appendChild($node);
        }

        $binding->appendChild($operation);

        return $operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:binding SOAP binding} element to a Binding element
     *
     * @param object $binding A binding XML_Tree_Node returned by {@link function addBinding}
     * @param string $style binding style, possible values are "rpc" (the default) and "document"
     * @param string $transport Transport method (defaults to HTTP)
     * @return boolean
     */
    public function addSoapBinding($binding, $style = 'document', $transport = 'http://schemas.xmlsoap.org/soap/http')
    {
        $soap_binding = $this->_dom->createElement('soap:binding');
        $soap_binding->setAttribute('style', $style);
        $soap_binding->setAttribute('transport', $transport);

        $binding->appendChild($soap_binding);

        return $soap_binding;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:operation SOAP operation} to an operation element
     *
     * @param object $operation An operation XML_Tree_Node returned by {@link function addBindingOperation}
     * @param string $soap_action SOAP Action
     * @return boolean
     */
    public function addSoapOperation($binding, $soap_action)
    {
        if ($soap_action instanceof Zend_Uri_Http) {
            $soap_action = $soap_action->getUri();
        }
        $soap_operation = $this->_dom->createElement('soap:operation');
        $soap_operation->setAttribute('soapAction', $soap_action);

        $binding->insertBefore($soap_operation, $binding->firstChild);

        return $soap_operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_services service} element to the WSDL
     *
     * @param string $name Service Name
     * @param string $port_name Name of the port for the service
     * @param string $binding Binding for the port
     * @param string $location SOAP Address for the service
     * @return object The new service's XML_Tree_Node for use with {@link function addDocumentation}
     */
    public function addService($name, $port_name, $binding, $location)
    {
        if ($location instanceof Zend_Uri_Http) {
            $location = $location->getUri();
        }
        $service = $this->_dom->createElement('service');
        $service->setAttribute('name', $name);

        $port = $this->_dom->createElement('port');
        $port->setAttribute('name', $port_name);
        $port->setAttribute('binding', $binding);

        $soap_address = $this->_dom->createElement('soap:address');
        $soap_address->setAttribute('location', $location);

        $port->appendChild($soap_address);
        $service->appendChild($port);

        $this->_wsdl->appendChild($service);

        return $service;
    }

    /**
     * Add a documentation element to any element in the WSDL.
     *
     * Note that the WSDL {@link http://www.w3.org/TR/wsdl#_documentation specification} uses 'document',
     * but the WSDL {@link http://schemas.xmlsoap.org/wsdl/ schema} uses 'documentation' instead.
     * The {@link http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html#WSDL_documentation_Element WS-I Basic Profile 1.1} recommends using 'documentation'.
     *
     * @param object $input_node An XML_Tree_Node returned by another method to add the documentation to
     * @param string $documentation Human readable documentation for the node
     * @return DOMElement The documentation element
     */
    public function addDocumentation($input_node, $documentation)
    {
        if ($input_node === $this) {
            $node = $this->_dom->documentElement;
        } else {
            $node = $input_node;
        }

        $doc = $this->_dom->createElement('documentation');
        $doc_cdata = $this->_dom->createTextNode(str_replace(array("\r\n", "\r"), "\n", $documentation));
        $doc->appendChild($doc_cdata);

        if($node->hasChildNodes()) {
            $node->insertBefore($doc, $node->firstChild);
        } else {
            $node->appendChild($doc);
        }

        return $doc;
    }

    /**
     * Add WSDL Types element
     *
     * @param object $types A DomDocument|DomNode|DomElement|DomDocumentFragment with all the XML Schema types defined in it
     */
    public function addTypes($types)
    {
        if ($types instanceof DomDocument) {
            $dom = $this->_dom->importNode($types->documentElement);
            $this->_wsdl->appendChild($types->documentElement);
        } elseif ($types instanceof DomNode || $types instanceof DomElement || $types instanceof DomDocumentFragment ) {
            $dom = $this->_dom->importNode($types);
            $this->_wsdl->appendChild($dom);
        }
    }

    /**
     * Add a complex type name that is part of this WSDL and can be used in signatures.
     *
     * @param string $type
     * @return Zend_Soap_Wsdl
     */
    public function addType($type)
    {
        if(!in_array($type, $this->_includedTypes)) {
            $this->_includedTypes[] = $type;
        }
        return $this;
    }

    /**
     * Return an array of all currently included complex types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->_includedTypes;
    }

    /**
     * Return the Schema node of the WSDL
     *
     * @return DOMElement
     */
    public function getSchema()
    {
        if($this->_schema == null) {
            $this->addSchemaTypeSection();
        }

        return $this->_schema;
    }

    /**
     * Return the WSDL as XML
     *
     * @return string WSDL as XML
     */
    public function toXML()
    {
           return $this->_dom->saveXML();
    }

    /**
     * Return DOM Document
     *
     * @return object DomDocum ent
     */
    public function toDomDocument()
    {
        return $this->_dom;
    }

    /**
     * Echo the WSDL as XML
     *
     * @return boolean
     */
    public function dump($filename = false)
    {
        if (!$filename) {
            echo $this->toXML();
            return true;
        } else {
            return file_put_contents($filename, $this->toXML());
        }
    }

    /**
     * Returns an XSD Type for the given PHP type
     *
     * @param string $type PHP Type to get the XSD type for
     * @return string
     */
    public function getType($type)
    {
        switch (strtolower($type)) {
            case 'string':
            case 'str':
                return 'xsd:string';
            case 'long':
                return 'xsd:long';
            case 'int':
            case 'integer':
                return 'xsd:int';
            case 'float':
                return 'xsd:float';
            case 'double':
                return 'xsd:double';
            case 'boolean':
            case 'bool':
                return 'xsd:boolean';
            case 'array':
                return 'soap-enc:Array';
            case 'object':
                return 'xsd:struct';
            case 'mixed':
                return 'xsd:anyType';
            case 'void':
                return '';
            default:
                // delegate retrieval of complex type to current strategy
                return $this->addComplexType($type);
            }
    }

    /**
     * This function makes sure a complex types section and schema additions are set.
     *
     * @return Zend_Soap_Wsdl
     */
    public function addSchemaTypeSection()
    {
        if ($this->_schema === null) {
            $this->_schema = $this->_dom->createElement('xsd:schema');
            $this->_schema->setAttribute('targetNamespace', $this->_uri);
            $types = $this->_dom->createElement('types');
            $types->appendChild($this->_schema);
            $this->_wsdl->appendChild($types);
        }
        return $this;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_types types} data type definition
     *
     * @param string $type Name of the class to be specified
     * @return string XSD Type for the given PHP type
     */
    public function addComplexType($type)
    {
        if (in_array($type, $this->getTypes())) {
            return "tns:$type";
        }
        $this->addSchemaTypeSection();

        $strategy = $this->getComplexTypeStrategy();
        $strategy->setContext($this);
        // delegates the detection of a complex type to the current strategy
        return $strategy->addComplexType($type);
    }

    /**
     * Parse an xsd:element represented as an array into a DOMElement.
     *
     * @param array $element an xsd:element represented as an array
     * @return DOMElement parsed element
     */
    private function _parseElement($element)
    {
        if (!is_array($element)) {
            #require_once "Zend/Soap/Wsdl/Exception.php";
            throw new Zend_Soap_Wsdl_Exception("The 'element' parameter needs to be an associative array.");
        }

        $elementXml = $this->_dom->createElement('xsd:element');
        foreach ($element as $key => $value) {
            if (in_array($key, array('sequence', 'all', 'choice'))) {
                if (is_array($value)) {
                    $complexType = $this->_dom->createElement('xsd:complexType');
                    if (count($value) > 0) {
                        $container = $this->_dom->createElement('xsd:' . $key);
                        foreach ($value as $subelement) {
                            $subelementXml = $this->_parseElement($subelement);
                            $container->appendChild($subelementXml);
                        }
                        $complexType->appendChild($container);
                    }
                    $elementXml->appendChild($complexType);
                }
            } else {
                $elementXml->setAttribute($key, $value);
            }
        }
        return $elementXml;
    }

    /**
     * Add an xsd:element represented as an array to the schema.
     *
     * Array keys represent attribute names and values their respective value.
     * The 'sequence', 'all' and 'choice' keys must have an array of elements as their value,
     * to add them to a nested complexType.
     *
     * Example: array( 'name' => 'MyElement',
     *                 'sequence' => array( array('name' => 'myString', 'type' => 'string'),
     *                                      array('name' => 'myInteger', 'type' => 'int') ) );
     * Resulting XML: <xsd:element name="MyElement"><xsd:complexType><xsd:sequence>
     *                  <xsd:element name="myString" type="string"/>
     *                  <xsd:element name="myInteger" type="int"/>
     *                </xsd:sequence></xsd:complexType></xsd:element>
     *
     * @param array $element an xsd:element represented as an array
     * @return string xsd:element for the given element array
     */
    public function addElement($element)
    {
        $schema = $this->getSchema();
        $elementXml = $this->_parseElement($element);
        $schema->appendChild($elementXml);
        return 'tns:' . $element['name'];
    }
}
