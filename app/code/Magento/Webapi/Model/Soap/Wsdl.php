<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Soap;

use DOMDocument;
use DOMDocumentFragment;
use DOMElement;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;
use Magento\Webapi\Api\Data\ComplexTypeStrategyInterface;
use Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy\DefaultComplexType;
use Laminas\Uri\Uri;
use RuntimeException;

/**
 * Class Wsdl
 */
class Wsdl
{
    /**#@+
     * XML Namespace uris and prefixes.
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
     * @var array
     */
    protected $classMap = [];

    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var array
     */
    protected $includedTypes = [];

    /**
     * @var DOMElement
     */
    protected $schema = null;

    /**
     * @var ComplexTypeStrategyInterface
     */
    protected $strategy = null;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var DOMElement
     */
    protected $wsdl;

    /**
     * @param string $name
     * @param string|Uri $uri
     * @param null|ComplexTypeStrategyInterface $strategy
     * @param null|array $classMap
     */
    public function __construct(
        string $name,
        $uri,
        ComplexTypeStrategyInterface $strategy = null,
        array $classMap = []
    ) {
        $this->setUri($uri);
        $this->classMap = $classMap;
        $this->dom = $this->getDOMDocument($name, $this->getUri());
        $this->wsdl = $this->dom->documentElement;
        $this->setComplexTypeStrategy($strategy ?: new DefaultComplexType());
    }

    /**
     * Get the wsdl XML document with all namespaces and required attributes.
     *
     * @param string|null $uri
     * @param string $name
     *
     * @return DOMDocument
     */
    protected function getDOMDocument(string $name, ?string $uri = null): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->resolveExternals = false;
        $dom->encoding = 'UTF-8';
        $dom->substituteEntities = false;

        $definitions = $dom->createElementNS(self::WSDL_NS_URI, 'definitions');
        $dom->appendChild($definitions);

        $uri = $this->sanitizeUri($uri);
        $this->setAttributeWithSanitization($definitions, 'name', $name);
        $this->setAttributeWithSanitization($definitions, 'targetNamespace', $uri);

        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::WSDL_NS, self::WSDL_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::TYPES_NS, $uri);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::SOAP_11_NS, self::SOAP_11_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::XSD_NS, self::XSD_NS_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::SOAP_ENC_NS, self::SOAP_ENC_URI);
        $definitions->setAttributeNS(self::XML_NS_URI, 'xmlns:'. self::SOAP_12_NS, self::SOAP_12_NS_URI);

        return $dom;
    }

    /**
     * Retrieve target namespace of the WSDL document.
     *
     * @return string|null
     */
    public function getTargetNamespace(): ?string
    {
        $targetNamespace = null;

        if ($this->wsdl !== null) {
            $targetNamespace = $this->wsdl->getAttribute('targetNamespace');
        }

        return $targetNamespace;
    }

    /**
     * Get the class map of php to wsdl mappings.
     *
     * @return array
     */
    public function getClassMap(): ?array
    {
        return $this->classMap;
    }

    /**
     * Set the class map of php to wsdl mappings.
     *
     * @param array $classMap
     *
     * @return Wsdl
     */
    public function setClassMap(array $classMap): Wsdl
    {
        $this->classMap = $classMap;

        return $this;
    }

    /**
     * Set a new uri for this WSDL.
     *
     * @param string|Uri $uri
     *
     * @return Wsdl
     */
    public function setUri($uri): Wsdl
    {
        if ($uri instanceof Uri) {
            $uri = $uri->toString();
        }

        $uri = $this->sanitizeUri($uri);
        $oldUri = $this->uri;
        $this->uri = $uri;

        if ($this->dom instanceof DOMDocument) {
            $this->dom->documentElement->setAttributeNS(self::XML_NS_URI, self::XML_NS . ':' . self::TYPES_NS, $uri);
            $xpath = new DOMXPath($this->dom);
            $xpath->registerNamespace('default', self::WSDL_NS_URI);
            $xpath->registerNamespace(self::TYPES_NS, $uri);
            $xpath->registerNamespace(self::SOAP_11_NS, self::SOAP_11_NS_URI);
            $xpath->registerNamespace(self::SOAP_12_NS, self::SOAP_12_NS_URI);
            $xpath->registerNamespace(self::XSD_NS, self::XSD_NS_URI);
            $xpath->registerNamespace(self::SOAP_ENC_NS, self::SOAP_ENC_URI);
            $xpath->registerNamespace(self::WSDL_NS, self::WSDL_NS_URI);
            $attributeNodes = $xpath->query('//attribute::*[contains(., "' . $oldUri . '")]');

            foreach ($attributeNodes as $node) {
                $attributeValue = $this->dom->createTextNode(str_replace($oldUri, $uri, $node->nodeValue));
                $node->replaceChild($attributeValue, $node->childNodes->item(0));
            }
        }

        return $this;
    }

    /**
     * Return WSDL uri.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Function for sanitizing uri.
     *
     * @param string|Uri $uri
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function sanitizeUri($uri): string
    {
        if ($uri instanceof Uri) {
            $uri = $uri->toString();
        }

        $uri = trim($uri);
        $uri = htmlspecialchars($uri, ENT_QUOTES, 'UTF-8', false);

        if (empty($uri)) {
            throw new InvalidArgumentException('Uri contains invalid characters or is empty');
        }

        return $uri;
    }

    /**
     * Set a strategy for complex type detection and handling.
     *
     * @param ComplexTypeStrategyInterface $strategy
     *
     * @return Wsdl
     */
    public function setComplexTypeStrategy(ComplexTypeStrategyInterface $strategy): Wsdl
    {
        $this->strategy = $strategy;

        return $this;
    }

    /**
     * Get the current complex type strategy.
     *
     * @return ComplexTypeStrategyInterface
     */
    public function getComplexTypeStrategy(): ?ComplexTypeStrategyInterface
    {
        return $this->strategy;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_messages message} element to the WSDL.
     *
     * @param string $messageName
     * @param array $parts
     *
     * @return DOMElement
     */
    public function addMessage(string $messageName, array $parts): DOMElement
    {
        $message = $this->dom->createElementNS(self::WSDL_NS_URI, 'message');
        $message->setAttribute('name', $messageName);

        if (count($parts) > 0) {
            foreach ($parts as $name => $type) {
                $part = $this->dom->createElementNS(self::WSDL_NS_URI, 'part');
                $message->appendChild($part);

                $part->setAttribute('name', $name);

                if (is_array($type)) {
                    $this->arrayToAttributes($part, $type);
                } else {
                    $this->setAttributeWithSanitization($part, 'type', $type);
                }
            }
        }

        $this->wsdl->appendChild($message);

        return $message;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_porttypes portType} element to the WSDL.
     *
     * @param string $name
     *
     * @return DOMElement
     */
    public function addPortType(string $name): DOMElement
    {
        $portType = $this->dom->createElementNS(self::WSDL_NS_URI, 'portType');
        $this->wsdl->appendChild($portType);
        $portType->setAttribute('name', $name);

        return $portType;
    }

    /**
     * Add an {@link http://www.w3.org/TR/wsdl#request-response operation} element to a portType element.
     *
     * @param DOMElement $portType
     * @param string $name
     * @param bool|string $input
     * @param bool|string $output
     * @param bool|string $fault
     *
     * @return DOMElement
     */
    public function addPortOperation(
        DOMElement $portType,
        string $name,
        $input = false,
        $output = false,
        $fault = false
    ): DOMElement {
        $operation = $this->dom->createElementNS(self::WSDL_NS_URI, 'operation');
        $portType->appendChild($operation);

        $operation->setAttribute('name', $name);

        if (is_string($input) && (strlen(trim($input)) >= 1)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'input');
            $operation->appendChild($node);
            $node->setAttribute('message', $input);
        }

        if (is_string($output) && (strlen(trim($output)) >= 1)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'output');
            $operation->appendChild($node);
            $node->setAttribute('message', $output);
        }

        if (is_string($fault) && (strlen(trim($fault)) >= 1)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'fault');
            $operation->appendChild($node);
            $node->setAttribute('message', $fault);
        }

        if (is_array($fault)) {
            $isMessageValid = isset(
                $fault['message']
            ) && is_string(
                $fault['message']
            ) && strlen(
                trim($fault['message'])
            );
            $isNameValid = isset($fault['name']) && is_string($fault['name']) && strlen(trim($fault['name']));

            if ($isNameValid && $isMessageValid) {
                $node = $this->toDomDocument()->createElement('fault');
                $node->setAttribute('name', $fault['name']);
                $node->setAttribute('message', $fault['message']);
                $operation->appendChild($node);
            }
        }

        return $operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_bindings binding} element to WSDL.
     *
     * @param string $name
     * @param string $portType
     *
     * @return DOMElement
     */
    public function addBinding(string $name, string $portType): DOMElement
    {
        $binding = $this->dom->createElementNS(self::WSDL_NS_URI, 'binding');
        $this->wsdl->appendChild($binding);

        $this->setAttribute($binding, 'name', $name);
        $this->setAttribute($binding, 'type', $portType);

        return $binding;
    }

    /**
     * Add an operation to a binding element.
     *
     * @param DOMElement $binding
     * @param string $name
     * @param array|bool $input
     * @param array|bool $output
     * @param array|bool $fault
     * @param int $soapVersion
     *
     * @return DOMElement
     */
    public function addBindingOperation(
        DOMElement $binding,
        string $name,
        $input = false,
        $output = false,
        $fault = false,
        int $soapVersion = SOAP_1_1
    ): DOMElement {
        $operation = $this->dom->createElementNS(self::WSDL_NS_URI, 'operation');
        $binding->appendChild($operation);

        $this->setAttribute($operation, 'name', $name);

        if (is_array($input) && ! empty($input)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'input');
            $operation->appendChild($node);

            $soapNode = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'body');
            $node->appendChild($soapNode);

            $this->arrayToAttributes($soapNode, $input);
        }

        if (is_array($output) && ! empty($output)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'output');
            $operation->appendChild($node);

            $soapNode = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'body');
            $node->appendChild($soapNode);

            $this->arrayToAttributes($soapNode, $output);
        }

        if (is_array($fault) && ! empty($fault)) {
            $node = $this->dom->createElementNS(self::WSDL_NS_URI, 'fault');
            $operation->appendChild($node);

            $this->arrayToAttributes($node, $fault);
        }

        return $operation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:binding SOAP binding} element to a Binding element.
     *
     * @param DOMElement $binding
     * @param string $style
     * @param string $transport
     * @param int $soapVersion
     *
     * @return DOMElement
     */
    public function addSoapBinding(
        DOMElement $binding,
        string $style = 'document',
        string $transport = 'http://schemas.xmlsoap.org/soap/http',
        int $soapVersion = SOAP_1_1
    ) {
        $soapBinding = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'binding');
        $binding->appendChild($soapBinding);

        $soapBinding->setAttribute('style', $style);
        $soapBinding->setAttribute('transport', $transport);

        return $soapBinding;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_soap:operation SOAP operation} to an operation element.
     *
     * @param DOMElement $operation
     * @param string $soapAction
     * @param int $soapVersion
     *
     * @return DOMElement
     */
    public function addSoapOperation(DOMElement $operation, string $soapAction, int $soapVersion = SOAP_1_1): DOMElement
    {
        if ($soapAction instanceof Uri) {
            $soapAction = $soapAction->toString();
        }
        $soapOperation = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'operation');
        $operation->insertBefore($soapOperation, $operation->firstChild);

        $this->setAttributeWithSanitization($soapOperation, 'soapAction', $soapAction);

        return $soapOperation;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_services service} element to the WSDL.
     *
     * @param string $name
     * @param string $portName
     * @param string $binding
     * @param string $location
     * @param int $soapVersion
     *
     * @return DOMElement
     */
    public function addService(
        string $name,
        string $portName,
        string $binding,
        string $location,
        int $soapVersion = SOAP_1_1
    ): DOMElement {
        if ($location instanceof Uri) {
            $location = $location->toString();
        }
        $service = $this->dom->createElementNS(WSDL::WSDL_NS_URI, 'service');
        $this->wsdl->appendChild($service);

        $service->setAttribute('name', $name);


        $port = $this->dom->createElementNS(WSDL::WSDL_NS_URI, 'port');
        $service->appendChild($port);

        $port->setAttribute('name', $portName);
        $port->setAttribute('binding', $binding);

        $soapAddress = $this->dom->createElementNS($this->getSoapNamespaceUriByVersion($soapVersion), 'address');
        $port->appendChild($soapAddress);

        $this->setAttributeWithSanitization($soapAddress, 'location', $location);
        return $service;
    }

    /**
     * Add a documentation element to any element in the WSDL.
     *
     * Note that the WSDL specification uses 'document', but the WSDL schema
     * uses 'documentation' instead.
     *
     * The WS-I Basic Profile 1.1 recommends using 'documentation'.
     *
     * @see http://www.w3.org/TR/wsdl#_documentation WSDL specification
     * @see http://schemas.xmlsoap.org/wsdl/ WSDL schema
     * @see http://www.ws-i.org/Profiles/BasicProfile-1.1-2004-08-24.html#WSDL_documentation_Element WS-I Basic
     *     Profile 1.1
     * @param DOMElement $inputNode
     * @param string $documentation
     *
     * @return DOMElement
     */
    public function addDocumentation(DOMElement $inputNode, string $documentation): DOMElement
    {
        if ($inputNode === $this) {
            $node = $this->dom->documentElement;
        } else {
            $node = $inputNode;
        }

        if ($node->namespaceURI == Wsdl::XSD_NS_URI) {
            // complex types require annotation element for documentation
            $doc = $this->dom->createElementNS(Wsdl::XSD_NS_URI, 'documentation');
            $child = $this->dom->createElementNS(Wsdl::XSD_NS_URI, 'annotation');
            $child->appendChild($doc);
        } else {
            $doc = $child = $this->dom->createElementNS(WSDL::WSDL_NS_URI, 'documentation');
        }
        if ($node->hasChildNodes()) {
            $node->insertBefore($child, $node->firstChild);
        } else {
            $node->appendChild($child);
        }

        $docCData = $this->dom->createTextNode(str_replace(["\r\n", "\r"], "\n", $documentation));
        $doc->appendChild($docCData);

        return $doc;
    }

    /**
     * Add WSDL Types element.
     *
     * @param DOMDocument|DOMNode|DOMElement|DOMDocumentFragment $types
     *
     * @return void
     */
    public function addTypes(DOMNode $types): void
    {
        if ($types instanceof DOMDocument) {
            $dom = $this->dom->importNode($types->documentElement);
            $this->wsdl->appendChild($dom);
        } elseif ($types instanceof DOMNode || $types instanceof DOMElement || $types instanceof DOMDocumentFragment) {
            $dom = $this->dom->importNode($types);
            $this->wsdl->appendChild($dom);
        }
    }

    /**
     * Add a complex type name that is part of this WSDL and can be used in signatures.
     *
     * @param string $type
     * @param string $wsdlType
     *
     * @return Wsdl
     */
    public function addType(string $type, string $wsdlType): Wsdl
    {
        if (!isset($this->includedTypes[$type])) {
            $this->includedTypes[$type] = $wsdlType;
        }

        return $this;
    }

    /**
     * Return an array of all currently included complex types.
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->includedTypes;
    }

    /**
     * Return the Schema node of the WSDL.
     *
     * @return DOMElement
     */
    public function getSchema(): ?DOMElement
    {
        if ($this->schema === null) {
            $this->addSchemaTypeSection();
        }

        return $this->schema;
    }

    /**
     * Return the WSDL as XML.
     *
     * @return string
     */
    public function toXML(): string
    {
        $this->dom->normalizeDocument();

        return $this->dom->saveXML();
    }

    /**
     * Return DOM Document.
     *
     * @return DOMDocument
     */
    public function toDomDocument(): DOMDocument
    {
        $this->dom->normalizeDocument();

        return $this->dom;
    }

    /**
     * Echo the WSDL as XML.
     *
     * @param bool $filename
     *
     * @return bool
     */
    public function dump(bool $filename = false): bool
    {
        $this->dom->normalizeDocument();

        if (! $filename) {
            echo $this->toXML();
            return true;
        }

        return (bool) file_put_contents($filename, $this->toXML());
    }

    /**
     * Returns an XSD Type for the given PHP type.
     *
     * @param string $type
     *
     * @return string
     */
    public function getType(string $type): string
    {
        switch (strtolower($type)) {
            case 'string':
            case 'str':
                return self::XSD_NS . ':string';

            case 'long':
                return self::XSD_NS . ':long';

            case 'int':
            case 'integer':
                return self::XSD_NS . ':int';

            case 'float':
                return self::XSD_NS . ':float';

            case 'double':
                return self::XSD_NS . ':double';

            case 'boolean':
            case 'bool':
                return self::XSD_NS . ':boolean';

            case 'array':
                return self::SOAP_ENC_NS . ':Array';

            case 'object':
                return self::XSD_NS . ':struct';

            case 'mixed':
                return self::XSD_NS . ':anyType';

            case 'date':
                return self::XSD_NS . ':date';

            case 'datetime':
                return self::XSD_NS . ':dateTime';

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
     * @return Wsdl
     */
    public function addSchemaTypeSection(): Wsdl
    {
        if ($this->schema === null) {
            $types = $this->dom->createElementNS(self::WSDL_NS_URI, 'types');
            $this->wsdl->appendChild($types);

            $this->schema = $this->dom->createElementNS(WSDL::XSD_NS_URI, 'schema');
            $types->appendChild($this->schema);

            $this->setAttributeWithSanitization($this->schema, 'targetNamespace', $this->getUri());
        }

        return $this;
    }

    /**
     * Translate PHP type into WSDL QName.
     *
     * @param string $type
     *
     * @return string
     */
    public function translateType(string $type): string
    {
        if (isset($this->classMap[$type])) {
            return $this->classMap[$type];
        }

        $type = trim($type, '\\');

        // remove namespace,
        $pos = strrpos($type, '\\');
        if ($pos) {
            $type = substr($type, $pos + 1);
        }

        return $type;
    }

    /**
     * Add a {@link http://www.w3.org/TR/wsdl#_types types} data type definition.
     *
     * @param string $type
     *
     * @return string
     */
    public function addComplexType(string $type): string
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
     * @param array $element
     *
     * @return DOMElement
     * @throws RuntimeException
     */
    protected function parseElement(array $element): DOMElement
    {
        if (! is_array($element)) {
            throw new RuntimeException('The "element" parameter needs to be an associative array.');
        }

        $elementXML = $this->dom->createElementNS(self::XSD_NS_URI, 'element');

        foreach ($element as $key => $value) {
            if (in_array($key, ['sequence', 'all', 'choice'])) {
                if (is_array($value)) {
                    $complexType = $this->dom->createElementNS(self::XSD_NS_URI, 'complexType');
                    if (count($value) > 0) {
                        $container = $this->dom->createElementNS(self::XSD_NS_URI, $key);
                        foreach ($value as $subElement) {
                            $subElementXML = $this->parseElement($subElement);
                            $container->appendChild($subElementXML);
                        }
                        $complexType->appendChild($container);
                    }
                    $elementXML->appendChild($complexType);
                }
            } else {
                $elementXML->setAttribute($key, $value);
            }
        }

        return $elementXML;
    }

    /**
     * Prepare attribute value for specific attributes.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return string
     */
    protected function sanitizeAttributeValueByName(string $name, $value): string
    {
        switch (strtolower($name)) {
            case 'targetnamespace':
            case 'encodingstyle':
            case 'soapaction':
            case 'location':
                return $this->sanitizeUri($value);

            default:
                return $value;
        }
    }

    /**
     * Convert associative array to attributes of given node
     *
     * Optionally uses {@link function sanitizeAttributeValueByName}.
     *
     * @param DOMNode $node
     * @param array $attributes
     * @param bool $withSanitizer
     *
     * @return void
     */
    protected function arrayToAttributes(DOMNode $node, array $attributes, bool $withSanitizer = true): void
    {
        foreach ($attributes as $attributeName => $attributeValue) {
            if ($withSanitizer) {
                $this->setAttributeWithSanitization($node, $attributeName, $attributeValue);
            } else {
                $this->setAttribute($node, $attributeName, $attributeValue);
            }
        }
    }

    /**
     * Set attribute to given node using {@link function sanitizeAttributeValueByName}.
     *
     * @param DOMNode $node
     * @param string $attributeName
     * @param mixed $attributeValue
     *
     * @return void
     */
    protected function setAttributeWithSanitization(DOMNode $node, string $attributeName, $attributeValue): void
    {
        $attributeValue = $this->sanitizeAttributeValueByName($attributeName, $attributeValue);
        $this->setAttribute($node, $attributeName, $attributeValue);
    }

    /**
     * Set attribute to given node
     *
     * @param DOMNode $node
     * @param string $attributeName
     * @param mixed $attributeValue
     *
     * @return void
     */
    protected function setAttribute(
        DOMNode $node,
        string $attributeName,
        $attributeValue
    ): void {
        $attributeNode = $node->ownerDocument->createAttribute($attributeName);
        $node->appendChild($attributeNode);

        $attributeNodeValue = $node->ownerDocument->createTextNode($attributeValue);
        $attributeNode->appendChild($attributeNodeValue);
    }

    /**
     * Return soap namespace uri according to $soapVersion.
     *
     * @param int $soapVersion
     *
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getSoapNamespaceUriByVersion(int $soapVersion): string
    {
        if ($soapVersion != SOAP_1_1 and $soapVersion != SOAP_1_2) {
            throw new InvalidArgumentException('Invalid SOAP version, use constants: SOAP_1_1 or SOAP_1_2');
        }

        if ($soapVersion == SOAP_1_1) {
            return self::SOAP_11_NS_URI;
        }

        return self::SOAP_12_NS_URI;
    }

    /**
     * Add an xsd:element represented as an array to the schema.
     *
     * Array keys represent attribute names and values their respective value.
     * The 'sequence', 'all' and 'choice' keys must have an array of elements as their value,
     * to add them to a nested complexType.
     *
     * Example:
     *
     * <code>
     * array(
     *     'name' => 'MyElement',
     *     'sequence' => array(
     *         array('name' => 'myString', 'type' => 'string'),
     *         array('name' => 'myInteger', 'type' => 'int')
     *     )
     * );
     * </code>
     *
     * Resulting XML:
     *
     * <code>
     * <xsd:element name="MyElement">
     *   <xsd:complexType><xsd:sequence>
     *     <xsd:element name="myString" type="string"/>
     *     <xsd:element name="myInteger" type="int"/>
     *   </xsd:sequence></xsd:complexType>
     * </xsd:element>
     * </code>
     *
     * @param array $element
     *
     * @return string
     */
    public function addElement(array $element): string
    {
        $schema = $this->getSchema();
        $elementXml = $this->parseElement($element);
        $schema->appendChild($elementXml);

        return self::TYPES_NS . ':' . $element['name'];
    }
}
