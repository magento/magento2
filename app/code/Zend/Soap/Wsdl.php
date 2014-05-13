<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Soap;

use DOMDocument;
use DOMElement;
use Zend\Soap\Wsdl\ComplexTypeStrategy\ComplexTypeStrategyInterface as ComplexTypeStrategy;
use Zend\Uri\Uri;

/**
 * \Zend\Soap\Wsdl
 *
 */
class Wsdl
{
    /**#@+
     * XML Namespaces.
     */
    const XML_NS = 'xmlns';
    const XML_NS_URI = 'http://www.w3.org/2000/xmlns/';
    const WSDL_NS = 'wsdl';
    const WSDL_NS_URI = 'http://schemas.xmlsoap.org/wsdl/';
    const SOAP_11_NS = 'soap';
    const SOAP_11_NS_URI = 'http://schemas.xmlsoap.org/wsdl/soap/';
    const SOAP_12_NS = 'soap12';
    const SOAP_12_NS_URI = 'http://schemas.xmlsoap.org/wsdl/soap12/';
    const SOAP_ENC_NS = 'soap-enc';
    const SOAP_ENC_URI = 'http://schemas.xmlsoap.org/soap/encoding/';
    const XSD_NS = 'xsd';
    const XSD_NS_URI = 'http://www.w3.org/2001/XMLSchema';
    const TYPES_NS = 'tns';
    /**#@-*/

    /**
     * @var object DomDocument Instance
     */
    private $dom;

    /**
     * @var object WSDL Root XML_Tree_Node
     */
    private $wsdl;

    /**
     * @var DOMElement
     */
    private $schema = null;

    /**
     * Types defined on schema
     *
     * @var array
     */
    private $includedTypes = array();

    /**
     * Strategy for detection of complex types
     */
    protected $strategy = null;

    /**
     * Map of PHP Class names to WSDL QNames.
     *
     * @var array
     */
    protected $classMap = array();

    /**
     * Constructor
     *
     * @param string  $name Name of the Web Service being Described
     * @param string|Uri $uri URI where the WSDL will be available
     * @param null|ComplexTypeStrategy $strategy Strategy for detection of complex types
     * @param null|array $classMap Map of PHP Class names to WSDL QNames
     * @throws Exception\RuntimeException
     */
    public function __construct($name, $uri, ComplexTypeStrategy $strategy = null, array $classMap = array())
    {
        if ($uri instanceof Uri) {
            $uri = $uri->toString();
        }
        $this->classMap = $classMap;
        $this->dom = new DOMDocument('1.0', 'utf-8');
        $targetNamespace = $this->_escapeUri($uri);
        $definitions = $this->dom->createElement('definitions');
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS, self::WSDL_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::TYPES_NS, $targetNamespace);
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::SOAP_11_NS, self::SOAP_11_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::SOAP_12_NS, self::SOAP_12_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::XSD_NS, self::XSD_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::SOAP_ENC_NS, self::SOAP_ENC_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::WSDL_NS, self::WSDL_NS_URI);
        $definitions->setAttribute('name', $name);
        $definitions->setAttribute('targetNamespace', $targetNamespace);
        $this->dom->appendChild($definitions);
        $this->wsdl = $this->dom->documentElement;
        $this->setComplexTypeStrategy($strategy ?: new Wsdl\ComplexTypeStrategy\DefaultComplexType);
    }

    /**
     * URL encode query part of the URI if it is present.
     *
     * @param string $uri
     * @return string
     */
    protected function _escapeUri($uri)
    {
        // normalize URL
        $uri = urldecode($uri);
        if (preg_match('/\?(.+)$/', $uri, $matches)) {
            $query = $matches[1];
            $uri = str_replace($query, urlencode($query), $uri);
        }

        return $uri;
    }

    /**
     * Convert encoded ampersand back to decoded value, to avoid double encoding by DOMElement::setAttribute()
     *
     * @param $uri
     * @return mixed
     */
    protected function _decodeAmpersand($uri)
    {
        return str_replace('&amp;', '&', $uri);
    }

    /**
     * Retrieve target namespace of the WSDL document.
     *
     * @return string
     */
    public function getTargetNamespace()
    {
        $targetNamespace = null;
        if ($this->wsdl !== null) {
            $targetNamespace = $this->wsdl->getAttribute('targetNamespace');
        }
        return $targetNamespace;
    }

    /**
     * Get the class map of php to wsdl qname types.
     *
     * @return array
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * Set the class map of php to wsdl qname types.
     */
    public function setClassMap($classMap)
    {
        $this->classMap = $classMap;
    }

    /**
     * Set a new uri for this WSDL
     *
     * @param  string|Uri $uri
     * @return \Zend\Soap\Wsdl
     */
    public function setUri($uri)
    {
        if ($uri instanceof Uri) {
            $uri = $uri->toString();
        }

        if ($this->wsdl !== null) {
            $targetNamespace = $this->_escapeUri($uri);
            $this->wsdl->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::TYPES_NS, $targetNamespace);
            $this->wsdl->setAttribute('targetNamespace', $targetNamespace);
            if ($this->schema !== null) {
                $this->schema->setAttribute('targetNamespace', $targetNamespace);
            }
        }

        return $this;
    }

    /**
     * Set a strategy for complex type detection and handling
     *
     * @param ComplexTypeStrategy $strategy
     * @return \Zend\Soap\Wsdl
     */
    public function setComplexTypeStrategy(ComplexTypeStrategy $strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * Get the current complex type strategy
     *
     * @return ComplexTypeStrategy
     */
    public function getComplexTypeStrategy()
    {
        return $this->strategy;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_messages message} element to the WSDL
     *
     * @param string $name Name for the {@link http://www.w3.org/TR/wsdl#_messages message}
     * @param array $parts An array of {@link http://www.w3.org/TR/wsdl#_message parts}
     *                     The array is constructed like: 'name of part' => 'part xml schema data type'
     *                     or 'name of part' => array('type' => 'part xml schema type')
     *                     or 'name of part' => array('element' => 'part xml element name')
     * @return DOMElement The new message's XML_Tree_Node for use in {@link function addDocumentation}
     */
    public function addMessage($name, $parts)
    {
        $message = $this->dom->createElement('message');

        $message->setAttribute('name', $name);

        if (count($parts) > 0) {
            foreach ($parts as $name => $type) {
                $part = $this->dom->createElement('part');
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

        $this->wsdl->appendChild($message);

        return $message;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_porttypes portType} element to the WSDL
     *
     * @param string $name portType element's name
     * @return DOMElement The new portType's XML_Tree_Node for use in {@link function addPortOperation} and {@link function addDocumentation}
     */
    public function addPortType($name)
    {
        $portType = $this->dom->createElement('portType');
        $portType->setAttribute('name', $name);
        $this->wsdl->appendChild($portType);

        return $portType;
    }

    /**
     * Add an {@link http://www.w3.org/TR/wsdl#request-response operation} element to a portType element
     *
     * @param DOMElement $portType a portType XML_Tree_Node, from {@link function addPortType}
     * @param string $name Operation name
     * @param bool|string $input Input Message
     * @param bool|string $output Output Message
     * @param bool|string $fault Fault Message
     * @return DOMElement The new operation's XML_Tree_Node for use in {@link function addDocumentation}
     */
    public function addPortOperation($portType, $name, $input = false, $output = false, $fault = false)
    {
        $operation = $this->dom->createElement('operation');
        $operation->setAttribute('name', $name);

        if (is_string($input) && (strlen(trim($input)) >= 1)) {
            $node = $this->dom->createElement('input');
            $node->setAttribute('message', $input);
            $operation->appendChild($node);
        }
        if (is_string($output) && (strlen(trim($output)) >= 1)) {
            $node= $this->dom->createElement('output');
            $node->setAttribute('message', $output);
            $operation->appendChild($node);
        }
        if (is_string($fault) && (strlen(trim($fault)) >= 1)) {
            $node = $this->dom->createElement('fault');
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
     * @param string $portType name of the portType to bind
     * @return DOMElement The new binding's XML_Tree_Node for use with {@link function addBindingOperation} and {@link function addDocumentation}
     */
    public function addBinding($name, $portType)
    {
        $binding = $this->dom->createElement('binding');
        $binding->setAttribute('name', $name);
        $binding->setAttribute('type', $portType);

        $this->wsdl->appendChild($binding);

        return $binding;
    }

    /**
     * Add an operation to a binding element
     *
     * @param DOMElement $binding A binding XML_Tree_Node returned by {@link function addBinding}
     * @param string $name
     * @param bool|array $input An array of attributes for the input element, allowed keys are: 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param bool|array $output An array of attributes for the output element, allowed keys are: 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param bool|array $fault An array of attributes for the fault element, allowed keys are: 'name', 'use', 'namespace', 'encodingStyle'. {@link http://www.w3.org/TR/wsdl#_soap:body More Information}
     * @param int $soapVersion SOAP version to be used in binding operation. 1.1 used by default.
     * @return DOMElement The new Operation's XML_Tree_Node for use with {@link function addSoapOperation} and {@link function addDocumentation}
     */
    public function addBindingOperation($binding, $name, $input = false, $output = false, $fault = false,
                                        $soapVersion = SOAP_1_1
    ) {
        $operation = $this->dom->createElement('operation');
        $operation->setAttribute('name', $name);

        $soapNs = $soapVersion == SOAP_1_1 ? self::SOAP_11_NS : self::SOAP_12_NS;
        if (is_array($input)) {
            $node = $this->dom->createElement('input');
            $soap_node = $this->dom->createElement($soapNs . ':body');
            foreach ($input as $name => $value) {
                $soap_node->setAttribute($name, $this->_decodeAmpersand($value));
            }
            $node->appendChild($soap_node);
            $operation->appendChild($node);
        }

        if (is_array($output)) {
            $node = $this->dom->createElement('output');
            $soap_node = $this->dom->createElement($soapNs . ':body');
            foreach ($output as $name => $value) {
                $soap_node->setAttribute($name, $this->_decodeAmpersand($value));
            }
            $node->appendChild($soap_node);
            $operation->appendChild($node);
        }

        if (is_array($fault)) {
            $node = $this->dom->createElement('fault');
            if (isset($fault['name'])) {
                $node->setAttribute('name', $fault['name']);
            }
            $soap_node = $this->dom->createElement($soapNs . ':fault');
            foreach ($fault as $name => $value) {
                $soap_node->setAttribute($name, $this->_decodeAmpersand($value));
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
     * @param DOMElement $binding A binding XML_Tree_Node returned by {@link function addBinding}
     * @param string $style binding style, possible values are "rpc" (the default) and "document"
     * @param string $transport Transport method (defaults to HTTP)
     * @param int $soapVersion SOAP version to be used in binding. 1.1 used by default.
     * @return DOMElement
     */
    public function addSoapBinding($binding, $style = 'document', $transport = 'http://schemas.xmlsoap.org/soap/http',
                                   $soapVersion = SOAP_1_1
    ) {
        $soapNs = $soapVersion == SOAP_1_1 ? self::SOAP_11_NS : self::SOAP_12_NS;
        $soap_binding = $this->dom->createElement($soapNs . ':binding');
        $soap_binding->setAttribute('style', $style);
        $soap_binding->setAttribute('transport', $transport);

        $binding->appendChild($soap_binding);

        return $soap_binding;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:operation SOAP operation} to an operation element
     *
     * @param DOMElement $operation An operation XML_Tree_Node returned by {@link function addBindingOperation}
     * @param string $soap_action SOAP Action
     * @param int $soapVersion SOAP version to be used in operation. 1.1 used by default.
     * @return DOMElement
     */
    public function addSoapOperation($operation, $soap_action, $soapVersion = SOAP_1_1)
    {
        if ($soap_action instanceof Uri) {
            $soap_action = $soap_action->toString();
        }
        $soapNs = $soapVersion ? self::SOAP_11_NS : self::SOAP_12_NS;
        $soap_operation = $this->dom->createElement($soapNs . ':operation');
        $soap_operation->setAttribute('soapAction', $this->_decodeAmpersand($soap_action));

        $operation->insertBefore($soap_operation, $operation->firstChild);

        return $soap_operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_services service} element to the WSDL
     *
     * @param string $name Service Name
     * @param string $port_name Name of the port for the service
     * @param string $binding Binding for the port
     * @param string $location SOAP Address for the service
     * @param int $soapVersion SOAP version to be used in service. 1.1 used by default.
     * @return DOMElement The new service's XML_Tree_Node for use with {@link function addDocumentation}
     */
    public function addService($name, $port_name, $binding, $location, $soapVersion = SOAP_1_1)
    {
        if ($location instanceof Uri) {
            $location = $location->toString();
        }
        $service = $this->dom->createElement('service');
        $service->setAttribute('name', $name);

        $port = $this->dom->createElement('port');
        $port->setAttribute('name', $port_name);
        $port->setAttribute('binding', $binding);

        $soapNs = $soapVersion ? self::SOAP_11_NS : self::SOAP_12_NS;
        $soap_address = $this->dom->createElement($soapNs . ':address');
        $soap_address->setAttribute('location', $this->_decodeAmpersand($location));

        $port->appendChild($soap_address);
        $service->appendChild($port);

        $this->wsdl->appendChild($service);

        return $service;
    }

    /**
     * Add a documentation element to any element in the WSDL.
     *
     * Note that the WSDL {@link http://www.w3.org/TR/wsdl#_documentation specification} uses 'document',
     * but the WSDL {@link http://schemas.xmlsoap.org/wsdl/ schema} uses 'documentation' instead.
     * The {@link http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html#WSDL_documentation_Element WS-I Basic Profile 1.1} recommends using 'documentation'.
     *
     * @param DOMElement $input_node An XML_Tree_Node returned by another method to add the documentation to
     * @param string $documentation Human readable documentation for the node
     * @return DOMElement The documentation element
     */
    public function addDocumentation($input_node, $documentation)
    {
        if ($input_node === $this) {
            $node = $this->dom->documentElement;
        } else {
            $node = $input_node;
        }

        $doc = $this->dom->createElement('documentation');
        $doc_cdata = $this->dom->createTextNode(str_replace(array("\r\n", "\r"), "\n", $documentation));
        $doc->appendChild($doc_cdata);

        if ($node->hasChildNodes()) {
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
        if ($types instanceof \DomDocument) {
            $dom = $this->dom->importNode($types->documentElement);
            $this->wsdl->appendChild($types->documentElement);
        } elseif ($types instanceof \DomNode || $types instanceof \DomElement || $types instanceof \DomDocumentFragment ) {
            $dom = $this->dom->importNode($types);
            $this->wsdl->appendChild($dom);
        }
    }

    /**
     * Add a complex type name that is part of this WSDL and can be used in signatures.
     *
     * @param string $type
     * @param string $wsdlType
     * @return \Zend\Soap\Wsdl
     */
    public function addType($type, $wsdlType)
    {
        if (!isset($this->includedTypes[$type])) {
            $this->includedTypes[$type] = $wsdlType;
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
        return $this->includedTypes;
    }

    /**
     * Return the Schema node of the WSDL
     *
     * @return DOMElement
     */
    public function getSchema()
    {
        if ($this->schema == null) {
            $this->addSchemaTypeSection();
        }

        return $this->schema;
    }

    /**
     * Return the WSDL as XML
     *
     * @return string WSDL as XML
     */
    public function toXML()
    {
        return $this->dom->saveXML();
    }

    /**
     * Return DOM Document
     *
     * @return object DomDocument
     */
    public function toDomDocument()
    {
        return $this->dom;
    }

    /**
     * Echo the WSDL as XML
     *
     * @param bool|string $filename
     * @return boolean
     */
    public function dump($filename = false)
    {
        if (!$filename) {
            echo $this->toXML();
            return true;
        }
        return file_put_contents($filename, $this->toXML());
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
     * @return \Zend\Soap\Wsdl
     */
    public function addSchemaTypeSection()
    {
        if ($this->schema === null) {
            $this->schema = $this->dom->createElement(self::XSD_NS . ':schema');
            $this->schema->setAttribute('targetNamespace', $this->getTargetNamespace());
            $types = $this->dom->createElement('types');
            $types->appendChild($this->schema);
            $this->wsdl->appendChild($types);
        }
        return $this;
    }

    /**
     * Translate PHP type into WSDL QName
     *
     * @param string $type
     * @return string QName
     */
    public function translateType($type)
    {
        if (isset($this->classMap[$type])) {
            return $this->classMap[$type];
        }

        if ($type[0] == '\\') {
            $type = substr($type, 1);
        }

        $pos = strrpos($type, '\\');
        if ($pos) {
            $type = substr($type, $pos+1);
        }

        return str_replace('\\', '.', $type);
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_types types} data type definition
     *
     * @param string $type Name of the class to be specified
     * @return string XSD Type for the given PHP type
     */
    public function addComplexType($type)
    {
        if (isset($this->includedTypes[$type])) {
            return $this->includedTypes[$type];
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
     * @throws Exception\RuntimeException if $element is not an array
     * @return DOMElement parsed element
     */
    private function _parseElement($element)
    {
        if (!is_array($element)) {
            throw new Exception\RuntimeException("The 'element' parameter needs to be an associative array.");
        }

        $elementXml = $this->dom->createElement(self::XSD_NS . ':element');
        foreach ($element as $key => $value) {
            if (in_array($key, array('sequence', 'all', 'choice'))) {
                if (is_array($value)) {
                    $complexType = $this->dom->createElement(self::XSD_NS . ':complexType');
                    if (count($value) > 0) {
                        $container = $this->dom->createElement(self::XSD_NS . ':' . $key);
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
        return self::TYPES_NS . ':' . $element['name'];
    }
}
