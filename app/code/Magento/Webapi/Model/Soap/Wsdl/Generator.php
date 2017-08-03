<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Soap\Wsdl;

use Magento\Webapi\Model\AbstractSchemaGenerator;
use Magento\Webapi\Model\Soap\Fault;
use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Model\Soap\WsdlFactory;
use Magento\Framework\Webapi\Authorization;
use Magento\Webapi\Model\ServiceMetadata;
use Magento\Framework\Exception\AuthorizationException;

/**
 * WSDL generator.
 * @since 2.0.0
 */
class Generator extends AbstractSchemaGenerator
{
    /** WSDL name */
    const WSDL_NAME = 'MagentoWSDL';

    /**
     * WSDL factory instance.
     *
     * @var WsdlFactory
     * @since 2.0.0
     */
    protected $_wsdlFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Model\Cache\Type\Webapi $cache
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator
     * @param \Magento\Webapi\Model\ServiceMetadata $serviceMetadata
     * @param Authorization $authorization
     * @param WsdlFactory $wsdlFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Webapi\Model\Cache\Type\Webapi $cache,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Webapi\CustomAttributeTypeLocatorInterface $customAttributeTypeLocator,
        \Magento\Webapi\Model\ServiceMetadata $serviceMetadata,
        Authorization $authorization,
        WsdlFactory $wsdlFactory
    ) {
        $this->_wsdlFactory = $wsdlFactory;
        parent::__construct(
            $cache,
            $typeProcessor,
            $customAttributeTypeLocator,
            $serviceMetadata,
            $authorization
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function generateSchema($requestedServiceMetadata, $requestScheme, $requestHost, $endPointUrl)
    {
        $wsdl = $this->_wsdlFactory->create(self::WSDL_NAME, $endPointUrl);
        $wsdl->addSchemaTypeSection();
        $faultMessageName = $this->_addGenericFaultComplexTypeNodes($wsdl);
        $wsdl = $this->addCustomAttributeTypes($wsdl);

        foreach ($requestedServiceMetadata as $serviceClass => &$serviceData) {
            $portTypeName = $this->getPortTypeName($serviceClass);
            $bindingName = $this->getBindingName($serviceClass);
            $portType = $wsdl->addPortType($portTypeName);
            $binding = $wsdl->addBinding($bindingName, Wsdl::TYPES_NS . ':' . $portTypeName);
            $wsdl->addSoapBinding($binding, 'document', 'http://schemas.xmlsoap.org/soap/http', SOAP_1_2);
            $portName = $this->getPortName($serviceClass);
            $serviceName = $this->getServiceName($serviceClass);
            $wsdl->addService($serviceName, $portName, Wsdl::TYPES_NS . ':' . $bindingName, $endPointUrl, SOAP_1_2);

            foreach ($serviceData[ServiceMetadata::KEY_SERVICE_METHODS] as $methodName => $methodData) {
                $operationName = $this->typeProcessor->getOperationName($serviceClass, $methodName);
                $bindingDataPrototype = ['use' => 'literal'];
                $inputBinding = $bindingDataPrototype;
                $inputMessageName = $this->_createOperationInput($wsdl, $operationName, $methodData);

                $outputMessageName = false;
                $outputBinding = false;
                if (isset($methodData['interface']['out']['parameters'])) {
                    $outputBinding = $bindingDataPrototype;
                    $outputMessageName = $this->_createOperationOutput($wsdl, $operationName, $methodData);
                }
                $faultBinding = ['name' => Fault::NODE_DETAIL_WRAPPER];

                $wsdl->addPortOperation(
                    $portType,
                    $operationName,
                    $inputMessageName,
                    $outputMessageName,
                    ['message' => $faultMessageName, 'name' => Fault::NODE_DETAIL_WRAPPER]
                );
                $bindingOperation = $wsdl->addBindingOperation(
                    $binding,
                    $operationName,
                    $inputBinding,
                    $outputBinding,
                    $faultBinding,
                    SOAP_1_2
                );
                $wsdl->addSoapOperation($bindingOperation, $operationName, SOAP_1_2);
            }
        }
        return $wsdl->toXML();
    }

    /**
     * Create and add WSDL Types for complex custom attribute classes
     *
     * @param \Magento\Webapi\Model\Soap\Wsdl $wsdl
     * @return \Magento\Webapi\Model\Soap\Wsdl
     * @since 2.0.0
     */
    protected function addCustomAttributeTypes($wsdl)
    {
        foreach ($this->customAttributeTypeLocator->getAllServiceDataInterfaces() as $customAttributeClass) {
            $typeName = $this->typeProcessor->register($customAttributeClass);
            $wsdl->addComplexType($this->typeProcessor->getArrayItemType($typeName));
        }
        return $wsdl;
    }

    /**
     * Create input message and corresponding element and complex types in WSDL.
     *
     * @param Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string input message name
     * @since 2.0.0
     */
    protected function _createOperationInput(Wsdl $wsdl, $operationName, $methodData)
    {
        $inputMessageName = $this->getInputMessageName($operationName);
        $complexTypeName = $this->getElementComplexTypeName($inputMessageName);
        $inputParameters = [];
        $elementData = [
            'name' => $inputMessageName,
            'type' => Wsdl::TYPES_NS . ':' . $complexTypeName,
        ];
        if (isset($methodData['interface']['in']['parameters'])) {
            $inputParameters = $methodData['interface']['in']['parameters'];
        } else {
            $elementData['nillable'] = 'true';
        }
        $wsdl->addElement($elementData);
        $callInfo = [];
        $callInfo['requiredInput']['yes']['calls'] = [$operationName];
        $typeData = [
            'documentation' => $methodData['documentation'],
            'parameters' => $inputParameters,
            'callInfo' => $callInfo,
        ];
        $this->typeProcessor->setTypeData($complexTypeName, $typeData);
        $wsdl->addComplexType($complexTypeName);
        $wsdl->addMessage(
            $inputMessageName,
            [
                'messageParameters' => [
                    'element' => Wsdl::TYPES_NS . ':' . $inputMessageName,
                ]
            ]
        );
        return Wsdl::TYPES_NS . ':' . $inputMessageName;
    }

    /**
     * Create output message and corresponding element and complex types in WSDL.
     *
     * @param Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string output message name
     * @since 2.0.0
     */
    protected function _createOperationOutput(Wsdl $wsdl, $operationName, $methodData)
    {
        $outputMessageName = $this->getOutputMessageName($operationName);
        $complexTypeName = $this->getElementComplexTypeName($outputMessageName);
        $wsdl->addElement(
            [
                'name' => $outputMessageName,
                'type' => Wsdl::TYPES_NS . ':' . $complexTypeName,
            ]
        );
        $callInfo = [];
        $callInfo['returned']['always']['calls'] = [$operationName];
        $typeData = [
            'documentation' => sprintf('Response container for the %s call.', $operationName),
            'parameters' => $methodData['interface']['out']['parameters'],
            'callInfo' => $callInfo,
        ];
        $this->typeProcessor->setTypeData($complexTypeName, $typeData);
        $wsdl->addComplexType($complexTypeName);
        $wsdl->addMessage(
            $outputMessageName,
            [
                'messageParameters' => [
                    'element' => Wsdl::TYPES_NS . ':' . $outputMessageName,
                ]
            ]
        );
        return Wsdl::TYPES_NS . ':' . $outputMessageName;
    }

    /**
     * Get name for service portType node.
     *
     * @param string $serviceName
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getServiceName($serviceName)
    {
        return $serviceName . 'Service';
    }

    /**
     * Get input message node name for operation.
     *
     * @param string $operationName
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOutputMessageName($operationName)
    {
        return $operationName . 'Response';
    }

    /**
     * Add WSDL elements related to generic SOAP fault, which are common for all operations: element, type and message.
     *
     * @param Wsdl $wsdl
     * @return string Default fault message name
     * @since 2.0.0
     */
    protected function _addGenericFaultComplexTypeNodes($wsdl)
    {
        $faultMessageName = Fault::NODE_DETAIL_WRAPPER;
        $complexTypeName = $this->getElementComplexTypeName($faultMessageName);
        $wsdl->addElement(
            [
                'name' => $faultMessageName,
                'type' => Wsdl::TYPES_NS . ':' . $complexTypeName,
            ]
        );
        $faultParamsComplexType = Fault::NODE_DETAIL_PARAMETER;
        $faultParamsData = [
            'parameters' => [
                Fault::NODE_DETAIL_PARAMETER_KEY => [
                    'type' => 'string',
                    'required' => true,
                    'documentation' => '',
                ],
                Fault::NODE_DETAIL_PARAMETER_VALUE => [
                    'type' => 'string',
                    'required' => true,
                    'documentation' => '',
                ],
            ],
        ];
        $wrappedErrorComplexType = Fault::NODE_DETAIL_WRAPPED_ERROR;
        $wrappedErrorData = [
            'parameters' => [
                Fault::NODE_DETAIL_WRAPPED_ERROR_MESSAGE => [
                    'type' => 'string',
                    'required' => true,
                    'documentation' => '',
                ],
                Fault::NODE_DETAIL_WRAPPED_ERROR_PARAMETERS => [
                    'type' => "{$faultParamsComplexType}[]",
                    'required' => false,
                    'documentation' => 'Message parameters.',
                ],
            ],
        ];
        $genericFaultTypeData = [
            'parameters' => [
                Fault::NODE_DETAIL_TRACE => [
                    'type' => 'string',
                    'required' => false,
                    'documentation' => 'Exception calls stack trace.',
                ],
                Fault::NODE_DETAIL_PARAMETERS => [
                    'type' => "{$faultParamsComplexType}[]",
                    'required' => false,
                    'documentation' => 'Additional exception parameters.',
                ],
                Fault::NODE_DETAIL_WRAPPED_ERRORS => [
                    'type' => "{$wrappedErrorComplexType}[]",
                    'required' => false,
                    'documentation' => 'Additional wrapped errors.',
                ],
            ],
        ];
        $this->typeProcessor->setTypeData($faultParamsComplexType, $faultParamsData);
        $this->typeProcessor->setTypeData($wrappedErrorComplexType, $wrappedErrorData);
        $this->typeProcessor->setTypeData($complexTypeName, $genericFaultTypeData);
        $wsdl->addComplexType($complexTypeName);
        $wsdl->addMessage(
            $faultMessageName,
            [
                'messageParameters' => [
                    'element' => Wsdl::TYPES_NS . ':' . $faultMessageName,
                ]
            ]
        );

        return Wsdl::TYPES_NS . ':' . $faultMessageName;
    }

    /**
     * Get service metadata
     *
     * @param string $serviceName
     * @return array
     * @since 2.0.0
     */
    protected function getServiceMetadata($serviceName)
    {
        return $this->serviceMetadata->getServiceMetadata($serviceName);
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    protected function getAllowedServicesMetadata($requestedServices)
    {
        $allowedServicesMetadata = parent::getAllowedServicesMetadata($requestedServices);
        if (!$allowedServicesMetadata) {
            throw new AuthorizationException(
                __(
                    'Consumer is not authorized to access %resources',
                    ['resources' => implode(', ', $requestedServices)]
                )
            );
        }
        return $allowedServicesMetadata;
    }
}
