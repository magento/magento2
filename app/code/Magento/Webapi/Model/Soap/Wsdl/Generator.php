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

use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Model\Soap\Fault;

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
     */
    public function __construct(
        \Magento\Webapi\Model\Soap\Config $apiConfig,
        \Magento\Webapi\Model\Soap\Wsdl\Factory $wsdlFactory,
        \Magento\Webapi\Model\Cache\Type $cache
    ) {
        $this->_apiConfig = $apiConfig;
        $this->_wsdlFactory = $wsdlFactory;
        $this->_cache = $cache;
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
        $this->_addDefaultFaultComplexTypeNodes($wsdl);
        foreach ($services as $serviceClass => $serviceData) {
            $portTypeName = $this->getPortTypeName($serviceClass);
            $bindingName = $this->getBindingName($serviceClass);
            $portType = $wsdl->addPortType($portTypeName);
            $binding = $wsdl->addBinding($bindingName, Wsdl::TYPES_NS . ':' . $portTypeName);
            $wsdl->addSoapBinding($binding, 'document', 'http://schemas.xmlsoap.org/soap/http', SOAP_1_2);
            $portName = $this->getPortName($serviceClass);
            $serviceName = $this->getServiceName($serviceClass);
            $wsdl->addService($serviceName, $portName, Wsdl::TYPES_NS
                . ':' . $bindingName, $endPointUrl, SOAP_1_2);

            foreach ($serviceData['methods'] as $methodName => $methodData) {
                $operationName = $this->getOperationName($serviceClass, $methodName);
                $inputBinding = array('use' => 'literal');
                $inputMessageName = $this->_createOperationInput($wsdl, $operationName, $methodData);

                $outputMessageName = false;
                $outputBinding = false;
                if (isset($methodData['interface']['outputComplexTypes'])) {
                    $outputBinding = array('use' => 'literal');
                    $outputMessageName = $this->_createOperationOutput($wsdl, $operationName, $methodData);
                }

                /** Default SOAP fault should be added to each operation declaration */
                $faultsInfo = array(
                    array(
                        'name' => Fault::NODE_DETAIL_WRAPPER,
                        'message' => Wsdl::TYPES_NS . ':' . $this->_getDefaultFaultMessageName()
                    )
                );
                if (isset($methodData['interface']['faultComplexTypes'])) {
                    $faultsInfo = array_merge(
                        $faultsInfo,
                        $this->_createOperationFaults($wsdl, $operationName, $methodData)
                    );
                }

                $wsdl->addPortOperation(
                    $portType,
                    $operationName,
                    $inputMessageName,
                    $outputMessageName,
                    $faultsInfo
                );
                $bindingOperation = $wsdl->addBindingOperation(
                    $binding,
                    $operationName,
                    $inputBinding,
                    $outputBinding,
                    $faultsInfo,
                    SOAP_1_2
                );
                $wsdl->addSoapOperation($bindingOperation, $operationName, SOAP_1_2);
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
                    $referencedType->value = Wsdl::TYPES_NS . ':' . $prefixedRefTypeName;
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
     * @param Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string input message name
     */
    protected function _createOperationInput(Wsdl $wsdl, $operationName, $methodData)
    {
        $inputMessageName = $this->getInputMessageName($operationName);
        $elementData = array(
            'name' => $inputMessageName,
            'type' => Wsdl::TYPES_NS . ':' . $inputMessageName
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
                    'element' => Wsdl::TYPES_NS . ':' . $inputMessageName
                )
            )
        );
        return Wsdl::TYPES_NS . ':' . $inputMessageName;
    }

    /**
     * Create output message, corresponding element and complex types in WSDL.
     *
     * @param Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string output message name
     */
    protected function _createOperationOutput(Wsdl $wsdl, $operationName, $methodData)
    {
        $outputMessageName = $this->getOutputMessageName($operationName);
        $wsdl->addElement(
            array(
                'name' => $outputMessageName,
                'type' => Wsdl::TYPES_NS . ':' . $outputMessageName
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
                    'element' => Wsdl::TYPES_NS . ':' . $outputMessageName
                )
            )
        );
        return Wsdl::TYPES_NS . ':' . $outputMessageName;
    }

    /**
     * Create an array of items that contain information about method faults.
     *
     * @param Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return array array(array('name' => ..., 'message' => ...))
     */
    protected function _createOperationFaults(Wsdl $wsdl, $operationName, $methodData)
    {
        $faults = array();
        if (isset($methodData['interface']['faultComplexTypes'])) {
            foreach ($methodData['interface']['faultComplexTypes'] as $faultName => $faultComplexTypes) {
                $faultMessageName = $this->getFaultMessageName($operationName, $faultName);
                $wsdl->addElement(
                    array(
                        'name' => $faultMessageName,
                        'type' => Wsdl::TYPES_NS . ':' . $faultMessageName
                    )
                );
                foreach ($faultComplexTypes as $complexTypeNode) {
                    $wsdl->addComplexType($complexTypeNode);
                }
                $wsdl->addMessage(
                    $faultMessageName,
                    array(
                        'messageParameters' => array(
                            'element' => Wsdl::TYPES_NS . ':' . $faultMessageName
                        )
                    )
                );
                $faults[] = array(
                    'name' => $operationName . $faultName,
                    'message' => Wsdl::TYPES_NS . ':' . $faultMessageName
                );
            }
        }
        return $faults;
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
     * Get fault message node name for operation.
     *
     * @param string $operationName
     * @param string $faultName
     * @return string
     */
    public function getFaultMessageName($operationName, $faultName)
    {
        return $operationName . $faultName . 'Fault';
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
     * Get info about complex types defined in the XSD for the service method faults.
     *
     * @param string $serviceMethod
     * @param \DOMDocument $domDocument
     * @return array array(array('complexTypeName' => ..., 'faultName' => ...))
     */
    public function getXsdFaultTypeNames($serviceMethod, $domDocument)
    {
        $faultTypeNames = array();
        $xpath = new \DOMXPath($domDocument);
        $serviceMethod = ucfirst($serviceMethod);
        $typeXPath = "//xsd:complexType[starts-with(@name,'{$serviceMethod}') and contains(@name,'Fault')]";
        $complexTypeNodes = $xpath->query($typeXPath);
        /** @var \DOMElement $complexTypeNode */
        foreach ($complexTypeNodes as $complexTypeNode) {
            $complexTypeName = $complexTypeNode->getAttribute('name');
            if (preg_match("/^{$serviceMethod}(\w+)Fault$/", $complexTypeName, $matches)) {
                $faultTypeNames[] = array('complexTypeName' => $complexTypeName, 'faultName' => $matches[1]);
            }
        }
        return $faultTypeNames;
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
            $methodInterface = array();
            $serviceMethod = $operationData[\Magento\Webapi\Model\Soap\Config::KEY_METHOD];
            /** @var $payloadSchemaDom \DOMDocument */
            $payloadSchemaDom = $this->_apiConfig->getServiceSchemaDOM($serviceClass);
            $operationName = $this->getOperationName($serviceName, $serviceMethod);

            /** Process input complex type */
            $inputParameterName = $this->getInputMessageName($operationName);
            $inputComplexTypes = $this->getComplexTypeNodes(
                $serviceName,
                $this->getXsdRequestTypeName($serviceMethod),
                $payloadSchemaDom
            );
            if (empty($inputComplexTypes)) {
                if ($operationData[\Magento\Webapi\Model\Soap\Config::KEY_IS_REQUIRED]) {
                    throw new \LogicException(
                        sprintf('The method "%s" of service "%s" must have "%s" complex type defined in its schema.',
                            $serviceMethod, $serviceName, $inputParameterName)
                    );
                } else {
                    /** Generate empty input request to make WSDL compliant with WS-I basic profile */
                    $inputComplexTypes[] = $this->_generateEmptyComplexType($inputParameterName, $payloadSchemaDom);
                }
            }
            $methodInterface['inputComplexTypes'] = $inputComplexTypes;

            /** Process output complex type */
            $outputParameterName = $this->getOutputMessageName($operationName);
            $outputComplexTypes = $this->getComplexTypeNodes(
                $serviceName,
                $this->getXsdResponseTypeName($serviceMethod),
                $payloadSchemaDom
            );
            if (!empty($outputComplexTypes)) {
                $methodInterface['outputComplexTypes'] = $outputComplexTypes;
            } else {
                throw new \LogicException(
                    sprintf('The method "%s" of service "%s" must have "%s" complex type defined in its schema.',
                        $serviceMethod, $serviceName, $outputParameterName)
                );
            }

            /** Process fault complex types */
            foreach ($this->getXsdFaultTypeNames($serviceMethod, $payloadSchemaDom) as $faultComplexType) {
                $faultComplexTypes = $this->_getFaultComplexTypeNodes(
                    $serviceName,
                    $faultComplexType['complexTypeName'],
                    $payloadSchemaDom
                );
                if (!empty($faultComplexTypes)) {
                    $methodInterface['faultComplexTypes'][$faultComplexType['faultName']] = $faultComplexTypes;
                }
            }
            $serviceDataTypes['methods'][$serviceMethod]['interface'] = $methodInterface;
        }
        return $serviceDataTypes;
    }

    /**
     * Add WSDL elements related to default SOAP fault, which are common for all operations: element, type and message.
     *
     * @param Wsdl $wsdl
     * @return \DOMNode[]
     */
    protected function _addDefaultFaultComplexTypeNodes($wsdl)
    {
        $domDocument = new \DOMDocument();
        $typeName = Fault::NODE_DETAIL_WRAPPER;
        $defaultFault = $this->_generateEmptyComplexType($typeName, $domDocument);
        $elementName = Fault::NODE_DETAIL_WRAPPER;
        $wsdl->addElement(array('name' => $elementName, 'type' => Wsdl::TYPES_NS . ':' . $typeName));
        $wsdl->addMessage(
            $this->_getDefaultFaultMessageName(),
            array('messageParameters' => array('element' => Wsdl::TYPES_NS . ':' . $elementName))
        );
        $this->_addDefaultFaultElements($defaultFault);
        $wsdl->addComplexType($defaultFault);
    }

    /**
     * Generate all necessary complex types for the fault of specified type.
     *
     * @param string $serviceName
     * @param string $typeName
     * @param \DOMDocument $domDocument
     * @return \DOMNode[]
     */
    protected function _getFaultComplexTypeNodes($serviceName, $typeName, $domDocument)
    {
        $complexTypesNodes = $this->getComplexTypeNodes($serviceName, $typeName, $domDocument);
        $faultTypeName = $serviceName . $typeName;
        $paramsTypeName = $faultTypeName . 'Params';
        if (isset($complexTypesNodes[$faultTypeName])) {
            /** Rename fault complex type to fault param complex type */
            $faultComplexType = $complexTypesNodes[$faultTypeName];
            $faultComplexType->setAttribute('name', $paramsTypeName);
            $complexTypesNodes[$paramsTypeName] = $complexTypesNodes[$faultTypeName];

            /** Create new fault complex type, which will contain reference to fault param complex type */
            $newFaultComplexType = $this->_generateEmptyComplexType($faultTypeName, $domDocument);
            $this->_addDefaultFaultElements($newFaultComplexType);
            /** Create 'Parameters' element and use fault param complex type as its type */
            $parametersElement = $domDocument->createElement('xsd:element');
            $parametersElement->setAttribute('name', Fault::NODE_DETAIL_PARAMETERS);
            $parametersElement->setAttribute('type', Wsdl::TYPES_NS . ':' . $paramsTypeName);
            $newFaultComplexType->firstChild->appendChild($parametersElement);

            $complexTypesNodes[$faultTypeName] = $newFaultComplexType;
        }
        return $complexTypesNodes;
    }

    /**
     * Generate empty complex type with the specified name.
     *
     * @param string $complexTypeName
     * @param \DOMDocument $domDocument
     * @return \DOMElement
     */
    protected function _generateEmptyComplexType($complexTypeName, $domDocument)
    {
        $complexTypeNode = $domDocument->createElement('xsd:complexType');
        $complexTypeNode->setAttribute('name', $complexTypeName);
        $xsdNamespace = 'http://www.w3.org/2001/XMLSchema';
        $complexTypeNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', $xsdNamespace);
        $domDocument->appendChild($complexTypeNode);
        $sequenceNode = $domDocument->createElement('xsd:sequence');
        $complexTypeNode->appendChild($sequenceNode);
        return $complexTypeNode;
    }

    /**
     * Add 'Detail' and 'Trace' elements to the fault element.
     *
     * @param \DOMElement $faultElement
     */
    protected function _addDefaultFaultElements($faultElement)
    {
        /** Create 'Code' element */
        $codeElement = $faultElement->ownerDocument->createElement('xsd:element');
        $codeElement->setAttribute('name', Fault::NODE_DETAIL_CODE);
        $codeElement->setAttribute('type', 'xsd:int');
        $faultElement->firstChild->appendChild($codeElement);

        /** Create 'Trace' element */
        $traceElement = $faultElement->ownerDocument->createElement('xsd:element');
        $traceElement->setAttribute('name', Fault::NODE_DETAIL_TRACE);
        $traceElement->setAttribute('type', 'xsd:string');
        $traceElement->setAttribute('minOccurs', '0');
        $faultElement->firstChild->appendChild($traceElement);
    }

    /**
     * Retrieve name of default SOAP fault message name in WSDL.
     *
     * @return string
     */
    protected function _getDefaultFaultMessageName()
    {
        return Fault::NODE_DETAIL_WRAPPER;
    }
}
