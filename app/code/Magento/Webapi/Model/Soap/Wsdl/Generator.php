<?php

/**
 * WSDL generator.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap\Wsdl;

use Zend\Soap\Wsdl;

class Generator
{
    const WSDL_NAME = 'MagentoWSDL';
    const WSDL_CACHE_ID = 'WSDL';

    /**
     * WSDL factory instance.
     *
     * @var \Magento\Webapi\Model\Soap\Wsdl\Factory
     */
    protected $_wsdlFactory;

    /**
     * @var \Magento\Webapi\Model\Cache\Type
     */
    protected $_cache;

    /**
     * @var \Magento\Webapi\Model\Soap\Config
     */
    protected $_apiConfig;

    /** @var \Magento\DomDocument\Factory */
    protected $_domDocumentFactory;

    /**
     * The list of registered complex types.
     *
     * @var string[]
     */
    protected $_registeredTypes = array();

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Model\Soap\Config $apiConfig
     * @param \Magento\Webapi\Model\Soap\Wsdl\Factory $wsdlFactory
     * @param \Magento\Webapi\Model\Cache\Type $cache
     * @param \Magento\DomDocument\Factory $domDocumentFactory
     */
    public function __construct(
        \Magento\Webapi\Model\Soap\Config $apiConfig,
        \Magento\Webapi\Model\Soap\Wsdl\Factory $wsdlFactory,
        \Magento\Webapi\Model\Cache\Type $cache,
        \Magento\DomDocument\Factory $domDocumentFactory
    ) {
        $this->_apiConfig = $apiConfig;
        $this->_wsdlFactory = $wsdlFactory;
        $this->_cache = $cache;
        $this->_domDocumentFactory = $domDocumentFactory;
    }

    /**
     * Generate WSDL file based on requested services (uses cache)
     *
     * @param array $requestedServices
     * @param string $endPointUrl
     * @return string
     * @throws \Magento\Webapi\Exception
     */
    public function generate($requestedServices, $endPointUrl)
    {
        /** Sort requested services by names to prevent caching of the same wsdl file more than once. */
        ksort($requestedServices);
        $cacheId = self::WSDL_CACHE_ID . hash('md5', serialize($requestedServices));
        $cachedWsdlContent = $this->_cache->load($cacheId);
        if ($cachedWsdlContent !== false) {
            return $cachedWsdlContent;
        }

        $wsdlContent = $this->_generate($requestedServices, $endPointUrl);
        $this->_cache->save($wsdlContent, $cacheId, array(\Magento\Webapi\Model\Cache\Type::CACHE_TAG));

        return $wsdlContent;
    }

    /**
     * Generate WSDL file based on requested services.
     *
     * @param array $requestedServices
     * @param string $endPointUrl
     * @return string
     * @throws \Magento\Webapi\Exception
     */
    protected function _generate($requestedServices, $endPointUrl)
    {
        $services = array();

        try {
            foreach ($requestedServices as $serviceName) {
                $services[$serviceName] = $this->_prepareServiceData($serviceName);
            }
        } catch (\Magento\Webapi\Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Magento\Webapi\Exception($e->getMessage());
        }

        $wsdl = $this->_wsdlFactory->create(self::WSDL_NAME, $endPointUrl);
        $wsdl->addSchemaTypeSection();

        foreach ($services as $serviceClass => $serviceData) {
            $portTypeName = $this->getPortTypeName($serviceClass);
            $bindingName = $this->getBindingName($serviceClass);
            $portType = $wsdl->addPortType($portTypeName);
            $binding = $wsdl->addBinding($bindingName, \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $portTypeName);
            $wsdl->addSoapBinding($binding, 'document', 'http://schemas.xmlsoap.org/soap/http', SOAP_1_2);
            $portName = $this->getPortName($serviceClass);
            $serviceName = $this->getServiceName($serviceClass);
            $wsdl->addService($serviceName, $portName, \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS
                . ':' . $bindingName, $endPointUrl, SOAP_1_2);

            foreach ($serviceData['methods'] as $methodName => $methodData) {
                $operationName = $this->getOperationName($serviceClass, $methodName);
                $inputBinding = array('use' => 'literal');
                $inputMessageName = $this->_createOperationInput($wsdl, $operationName, $methodData);

                $outputMessageName = false;
                $outputBinding = false;
                if (isset($methodData['interface']['outputComplexTypes'])) {
                    $outputBinding = $inputBinding;
                    $outputMessageName = $this->_createOperationOutput($wsdl, $operationName, $methodData);
                }

                $wsdl->addPortOperation($portType, $operationName, $inputMessageName, $outputMessageName);
                $bindingOperation = $wsdl->addBindingOperation(
                    $binding,
                    $operationName,
                    $inputBinding,
                    $outputBinding,
                    false,
                    SOAP_1_2
                );
                $wsdl->addSoapOperation($bindingOperation, $operationName, SOAP_1_2);
                // @TODO: implement faults binding
            }
        }
        return $wsdl->toXML();
    }

    /**
     * Extract complex type element from dom document by type name (include referenced types as well).
     *
     * @param string $serviceName
     * @param string $typeName Type names as defined in Service XSDs
     * @param \DOMDocument $domDocument
     * @return \DOMNode[]
     */
    public function getComplexTypeNodes($serviceName, $typeName, $domDocument)
    {
        $response = array();
        /** TODO: Use object manager to instantiate objects */
        $xpath = new \DOMXPath($domDocument);
        $typeXPath = "//xsd:complexType[@name='{$typeName}']";
        $complexTypeNodes = $xpath->query($typeXPath);
        if ($complexTypeNodes) {
            $complexTypeNode = $complexTypeNodes->item(0);
        }
        if (isset($complexTypeNode)) {
            $this->_registeredTypes[] = $serviceName . $typeName;

            $referencedTypes = $xpath->query("{$typeXPath}//@type");
            foreach ($referencedTypes as $referencedType) {
                $referencedTypeName = $referencedType->value;
                $prefixedRefTypeName = $serviceName . $referencedTypeName;
                if ($this->isComplexType($referencedTypeName, $domDocument)
                    && !in_array($prefixedRefTypeName, $this->_registeredTypes)
                ) {
                    $response += $this->getComplexTypeNodes($serviceName, $referencedTypeName, $domDocument);
                    /** Add target namespace to the referenced type name */
                    $referencedType->value = \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $prefixedRefTypeName;
                }
            }
            $complexTypeNode->setAttribute(
                'name',
                $serviceName . $typeName
            );
            $response[$serviceName . $typeName]
                = $complexTypeNode->cloneNode(true);
        }
        return $response;
    }

    /**
     * Check if provided type is complex or simple type.
     *
     * Current implementation is based on the assumption that complex types are not prefixed with any namespace,
     * and simple types are prefixed.
     *
     * @param string $typeName
     * @return bool
     */
    public function isComplexType($typeName)
    {
        return !strpos($typeName, ':');
    }

    /**
     * Create input message and corresponding element and complex types in WSDL.
     *
     * @param \Magento\Webapi\Model\Soap\Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string input message name
     */
    protected function _createOperationInput(\Magento\Webapi\Model\Soap\Wsdl $wsdl, $operationName, $methodData)
    {
        $inputMessageName = $this->getInputMessageName($operationName);
        $elementData = array(
            'name' => $inputMessageName,
            'type' => \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $inputMessageName
        );
        if (isset($methodData['interface']['inputComplexTypes'])) {
            foreach ($methodData['interface']['inputComplexTypes'] as $complexTypeNode) {
                $wsdl->addComplexType($complexTypeNode);
            }
        } else {
            $elementData['nillable'] = 'true';
        }
        $wsdl->addElement($elementData);
        $wsdl->addMessage(
            $inputMessageName,
            array(
                'messageParameters' => array(
                    'element' => \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $inputMessageName
                )
            )
        );
        return \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $inputMessageName;
    }

    /**
     * Create output message and corresponding element and complex types in WSDL.
     *
     * @param \Magento\Webapi\Model\Soap\Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string output message name
     */
    protected function _createOperationOutput(\Magento\Webapi\Model\Soap\Wsdl $wsdl, $operationName, $methodData)
    {
        $outputMessageName = $this->getOutputMessageName($operationName);
        $wsdl->addElement(
            array(
                'name' => $outputMessageName,
                'type' => \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $outputMessageName
            )
        );
        if (isset($methodData['interface']['outputComplexTypes'])) {
            foreach ($methodData['interface']['outputComplexTypes'] as $complexTypeNode) {
                $wsdl->addComplexType($complexTypeNode);
            }
        }
        $wsdl->addMessage(
            $outputMessageName,
            array(
                'messageParameters' => array(
                    'element' => \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $outputMessageName
                )
            )
        );
        return \Magento\Webapi\Model\Soap\Wsdl::TYPES_NS . ':' . $outputMessageName;
    }

    /**
     * Get name of complexType for message element.
     *
     * @param string $messageName
     * @return string
     */
    public function getElementComplexTypeName($messageName)
    {
        return ucfirst($messageName);
    }

    /**
     * Get name for service portType node.
     *
     * @param string $serviceName
     * @return string
     */
    public function getPortTypeName($serviceName)
    {
        return $serviceName . 'PortType';
    }

    /**
     * Get name for service binding node.
     *
     * @param string $serviceName
     * @return string
     */
    public function getBindingName($serviceName)
    {
        return $serviceName . 'Binding';
    }

    /**
     * Get name for service port node.
     *
     * @param string $serviceName
     * @return string
     */
    public function getPortName($serviceName)
    {
        return $serviceName . 'Port';
    }

    /**
     * Get name for service service.
     *
     * @param string $serviceName
     * @return string
     */
    public function getServiceName($serviceName)
    {
        return $serviceName . 'Service';
    }

    /**
     * Get name of operation based on service and method names.
     *
     * @param string $serviceName
     * @param string $methodName
     * @return string
     */
    public function getOperationName($serviceName, $methodName)
    {
        return $serviceName . ucfirst($methodName);
    }

    /**
     * Get input message node name for operation.
     *
     * @param string $operationName
     * @return string
     */
    public function getInputMessageName($operationName)
    {
        return $operationName . 'Request';
    }

    /**
     * Get output message node name for operation.
     *
     * @param string $operationName
     * @return string
     */
    public function getOutputMessageName($operationName)
    {
        return $operationName . 'Response';
    }

    /**
     * Get complexType name defined in the XSD for requests
     *
     * @param $serviceMethod
     * @return string
     */
    public function getXsdRequestTypeName($serviceMethod)
    {
        return ucfirst($serviceMethod) . "Request";
    }

    /**
     * Get complexType name defined in the XSD for responses
     *
     * @param $serviceMethod
     * @return string
     */
    public function getXsdResponseTypeName($serviceMethod)
    {
        return ucfirst($serviceMethod) . "Response";
    }

    /**
     * Prepare data about requested service for WSDL generator.
     *
     * @param string $serviceName
     * @return array
     * @throws \Magento\Webapi\Exception
     * @throws \LogicException
     */
    protected function _prepareServiceData($serviceName)
    {
        $requestedServices = $this->_apiConfig->getRequestedSoapServices(array($serviceName));
        if (empty($requestedServices)) {
            throw new \Magento\Webapi\Exception(
                __('Service %1 is not available.', $serviceName),
                0,
                \Magento\Webapi\Exception::HTTP_NOT_FOUND
            );
        }
        /** $requestedServices is expected to contain exactly one item */
        $serviceData = reset($requestedServices);
        $serviceDataTypes = array('methods' => array());
        $serviceClass = $serviceData[\Magento\Webapi\Model\Soap\Config::KEY_CLASS];
        foreach ($serviceData['methods'] as $operationData) {
            $serviceMethod = $operationData[\Magento\Webapi\Model\Soap\Config::KEY_METHOD];
            /** @var $payloadSchemaDom \DOMDocument */
            $payloadSchemaDom = $this->_apiConfig->getServiceSchemaDOM($serviceClass);
            $operationName = $this->getOperationName($serviceName, $serviceMethod);
            $inputParameterName = $this->getInputMessageName($operationName);
            $inputComplexTypes = $this->getComplexTypeNodes($serviceName,
                $this->getXsdRequestTypeName($serviceMethod),
                $payloadSchemaDom);
            if (empty($inputComplexTypes)) {
                if ($operationData[\Magento\Webapi\Model\Soap\Config::KEY_IS_REQUIRED]) {
                    throw new \LogicException(
                        sprintf('The method "%s" of service "%s" must have "%s" complex type defined in its schema.',
                            $serviceMethod, $serviceName, $inputParameterName)
                    );
                } else {
                    /** Generate empty input request to make WSDL compliant with WS-I basic profile */
                    $inputComplexTypes[] = $this->_generateEmptyComplexType($inputParameterName);
                }
            }
            $serviceDataTypes['methods'][$serviceMethod]['interface']['inputComplexTypes'] = $inputComplexTypes;
            $outputParameterName = $this->getOutputMessageName($operationName);
            $outputComplexTypes = $this->getComplexTypeNodes($serviceName,
                $this->getXsdResponseTypeName($serviceMethod),
                $payloadSchemaDom);
            if (!empty($outputComplexTypes)) {
                $serviceDataTypes['methods'][$serviceMethod]['interface']['outputComplexTypes'] = $outputComplexTypes;
            } else {
                throw new \LogicException(
                    sprintf('The method "%s" of service "%s" must have "%s" complex type defined in its schema.',
                        $serviceMethod, $serviceName, $outputParameterName)
                );
            }
        }
        return $serviceDataTypes;
    }

    /**
     * Generate empty complex type with the specified name.
     *
     * @param string $complexTypeName
     * @return \DOMElement
     */
    protected function _generateEmptyComplexType($complexTypeName)
    {
        $domDocument = $this->_domDocumentFactory->createDomDocument();
        $complexTypeNode = $domDocument->createElement('xsd:complexType');
        $complexTypeNode->setAttribute('name', $complexTypeName);
        $xsdNamespace = 'http://www.w3.org/2001/XMLSchema';
        $complexTypeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', $xsdNamespace);
        $domDocument->appendChild($complexTypeNode);
        $sequenceNode = $domDocument->createElement('xsd:sequence');
        $complexTypeNode->appendChild($sequenceNode);
        return $complexTypeNode;
    }
}
