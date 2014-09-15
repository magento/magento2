<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Soap\Wsdl;

use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Model\Soap\Fault;

/**
 * WSDL generator.
 */
class Generator
{
    const WSDL_NAME = 'MagentoWSDL';
    const WSDL_CACHE_ID = 'WSDL';
    /**
     * WSDL factory instance.
     *
     * @var Factory
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

    /** @var \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor */
    protected $_typeProcessor;

    /**
     * The list of registered complex types.
     *
     * @var string[]
     */
    protected $_registeredTypes = array();

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Model\Soap\Config $apiConfig
     * @param Factory $wsdlFactory
     * @param \Magento\Webapi\Model\Cache\Type $cache
     * @param \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor $typeProcessor
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Webapi\Model\Soap\Config $apiConfig,
        Factory $wsdlFactory,
        \Magento\Webapi\Model\Cache\Type $cache,
        \Magento\Webapi\Model\Config\ClassReflector\TypeProcessor $typeProcessor,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_apiConfig = $apiConfig;
        $this->_wsdlFactory = $wsdlFactory;
        $this->_cache = $cache;
        $this->_typeProcessor = $typeProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Generate WSDL file based on requested services (uses cache)
     *
     * @param array $requestedServices
     * @param string $endPointUrl
     * @return string
     * @throws \Exception
     */
    public function generate($requestedServices, $endPointUrl)
    {
        /** Sort requested services by names to prevent caching of the same wsdl file more than once. */
        ksort($requestedServices);
        $currentStore = $this->storeManager->getStore();
        $cacheId = self::WSDL_CACHE_ID . hash('md5', serialize($requestedServices) . $currentStore->getCode());
        $cachedWsdlContent = $this->_cache->load($cacheId);
        if ($cachedWsdlContent !== false) {
            return $cachedWsdlContent;
        }
        $services = array();
        foreach ($requestedServices as $serviceName) {
            $services[$serviceName] = $this->_apiConfig->getServiceMetadata($serviceName);
        }

        $wsdlContent = $this->_generate($services, $endPointUrl);
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
        $this->_collectCallInfo($requestedServices);
        $wsdl = $this->_wsdlFactory->create(self::WSDL_NAME, $endPointUrl);
        $wsdl->addSchemaTypeSection();
        $faultMessageName = $this->_addGenericFaultComplexTypeNodes($wsdl);
        foreach ($requestedServices as $serviceClass => $serviceData) {
            $portTypeName = $this->getPortTypeName($serviceClass);
            $bindingName = $this->getBindingName($serviceClass);
            $portType = $wsdl->addPortType($portTypeName);
            $binding = $wsdl->addBinding($bindingName, Wsdl::TYPES_NS . ':' . $portTypeName);
            $wsdl->addSoapBinding($binding, 'document', 'http://schemas.xmlsoap.org/soap/http', SOAP_1_2);
            $portName = $this->getPortName($serviceClass);
            $serviceName = $this->getServiceName($serviceClass);
            $wsdl->addService($serviceName, $portName, Wsdl::TYPES_NS . ':' . $bindingName, $endPointUrl, SOAP_1_2);

            foreach ($serviceData['methods'] as $methodName => $methodData) {
                $operationName = $this->getOperationName($serviceClass, $methodName);
                $bindingDataPrototype = array('use' => 'literal');
                $inputBinding = $bindingDataPrototype;
                $inputMessageName = $this->_createOperationInput($wsdl, $operationName, $methodData);

                $outputMessageName = false;
                $outputBinding = false;
                if (isset($methodData['interface']['out']['parameters'])) {
                    $outputBinding = $bindingDataPrototype;
                    $outputMessageName = $this->_createOperationOutput($wsdl, $operationName, $methodData);
                }
                $faultBinding = array_merge($bindingDataPrototype, array('name' => Fault::NODE_DETAIL_WRAPPER));

                $wsdl->addPortOperation(
                    $portType,
                    $operationName,
                    $inputMessageName,
                    $outputMessageName,
                    array('message' => $faultMessageName, 'name' => Fault::NODE_DETAIL_WRAPPER)
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
        $complexTypeName = $this->getElementComplexTypeName($inputMessageName);
        $inputParameters = array();
        $elementData = array(
            'name' => $inputMessageName,
            'type' => Wsdl::TYPES_NS . ':' . $complexTypeName
        );
        if (isset($methodData['interface']['in']['parameters'])) {
            $inputParameters = $methodData['interface']['in']['parameters'];
        } else {
            $elementData['nillable'] = 'true';
        }
        $wsdl->addElement($elementData);
        $callInfo = array();
        $callInfo['requiredInput']['yes']['calls'] = array($operationName);
        $typeData = array(
            'documentation' => $methodData['documentation'],
            'parameters' => $inputParameters,
            'callInfo' => $callInfo,
        );
        $this->_typeProcessor->setTypeData($complexTypeName, $typeData);
        $wsdl->addComplexType($complexTypeName);
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
     * Create output message and corresponding element and complex types in WSDL.
     *
     * @param Wsdl $wsdl
     * @param string $operationName
     * @param array $methodData
     * @return string output message name
     */
    protected function _createOperationOutput(Wsdl $wsdl, $operationName, $methodData)
    {
        $outputMessageName = $this->getOutputMessageName($operationName);
        $complexTypeName = $this->getElementComplexTypeName($outputMessageName);
        $wsdl->addElement(
            array(
                'name' => $outputMessageName,
                'type' => Wsdl::TYPES_NS . ':' . $complexTypeName
            )
        );
        $callInfo = array();
        $callInfo['returned']['always']['calls'] = array($operationName);
        $typeData = array(
            'documentation' => sprintf('Response container for the %s call.', $operationName),
            'parameters' => $methodData['interface']['out']['parameters'],
            'callInfo' => $callInfo,
        );
        $this->_typeProcessor->setTypeData($complexTypeName, $typeData);
        $wsdl->addComplexType($complexTypeName);
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
     * Collect data about complex types call info.
     *
     * Walks through all requested services and checks all methods 'in' and 'out' parameters.
     *
     * @param array $requestedServices
     * @return void
     */
    protected function _collectCallInfo($requestedServices)
    {
        foreach ($requestedServices as $serviceName => $serviceData) {
            foreach ($serviceData['methods'] as $methodName => $methodData) {
                $this->_processInterfaceCallInfo($methodData['interface'], $serviceName, $methodName);
            }
        }
    }

    /**
     * Process call info data from interface.
     *
     * @param array $interface
     * @param string $serviceName
     * @param string $methodName
     * @return void
     */
    protected function _processInterfaceCallInfo($interface, $serviceName, $methodName)
    {
        foreach ($interface as $direction => $interfaceData) {
            $direction = ($direction == 'in') ? 'requiredInput' : 'returned';
            foreach ($interfaceData['parameters'] as $parameterData) {
                $parameterType = $parameterData['type'];
                if (!$this->_typeProcessor->isTypeSimple($parameterType)
                    && !$this->_typeProcessor->isTypeAny($parameterType)
                ) {
                    $operation = $this->getOperationName($serviceName, $methodName);
                    if ($parameterData['required']) {
                        $condition = ($direction == 'requiredInput') ? 'yes' : 'always';
                    } else {
                        $condition = ($direction == 'requiredInput') ? 'no' : 'conditionally';
                    }
                    $callInfo = array();
                    $callInfo[$direction][$condition]['calls'][] = $operation;
                    $this->_typeProcessor->setTypeData($parameterType, array('callInfo' => $callInfo));
                }
            }
        }
    }

    /**
     * Add WSDL elements related to generic SOAP fault, which are common for all operations: element, type and message.
     *
     * @param Wsdl $wsdl
     * @return string Default fault message name
     */
    protected function _addGenericFaultComplexTypeNodes($wsdl)
    {
        $faultMessageName = Fault::NODE_DETAIL_WRAPPER;
        $complexTypeName = $this->getElementComplexTypeName($faultMessageName);
        $wsdl->addElement(
            array(
                'name' => $faultMessageName,
                'type' => Wsdl::TYPES_NS . ':' . $complexTypeName
            )
        );
        $faultParamsComplexType = Fault::NODE_DETAIL_PARAMETER;
        $faultParamsData = array(
            'parameters' => array(
                Fault::NODE_DETAIL_PARAMETER_KEY => array(
                    'type' => 'string',
                    'required' => true,
                    'documentation' => '',
                ),
                Fault::NODE_DETAIL_PARAMETER_VALUE => array(
                    'type' => 'string',
                    'required' => true,
                    'documentation' => '',
                )
            )
        );
        $wrappedErrorComplexType = Fault::NODE_DETAIL_WRAPPED_ERROR;
        $wrappedErrorData = array(
            'parameters' => array(
                Fault::NODE_DETAIL_WRAPPED_ERROR_MESSAGE => array(
                    'type' => 'string',
                    'required' => true,
                    'documentation' => '',
                ),
                Fault::NODE_DETAIL_WRAPPED_ERROR_PARAMETERS => array(
                    'type' => "{$faultParamsComplexType}[]",
                    'required' => false,
                    'documentation' => 'Message parameters.',
                ),
            )
        );
        $genericFaultTypeData = array(
            'parameters' => array(
                Fault::NODE_DETAIL_TRACE => array(
                    'type' => 'string',
                    'required' => false,
                    'documentation' => 'Exception calls stack trace.',
                ),
                Fault::NODE_DETAIL_PARAMETERS => array(
                    'type' => "{$faultParamsComplexType}[]",
                    'required' => false,
                    'documentation' => 'Additional exception parameters.',
                ),
                Fault::NODE_DETAIL_WRAPPED_ERRORS => array(
                    'type' => "{$wrappedErrorComplexType}[]",
                    'required' => false,
                    'documentation' => 'Additional wrapped errors.',
                )
            )
        );
        $this->_typeProcessor->setTypeData($faultParamsComplexType, $faultParamsData);
        $this->_typeProcessor->setTypeData($wrappedErrorComplexType, $wrappedErrorData);
        $this->_typeProcessor->setTypeData($complexTypeName, $genericFaultTypeData);
        $wsdl->addComplexType($complexTypeName);
        $wsdl->addMessage(
            $faultMessageName,
            array(
                'messageParameters' => array(
                    'element' => Wsdl::TYPES_NS . ':' . $faultMessageName
                )
            )
        );

        return Wsdl::TYPES_NS . ':' . $faultMessageName;
    }
}
