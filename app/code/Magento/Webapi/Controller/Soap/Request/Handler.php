<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller\Soap\Request;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Webapi\Controller\ServiceArgsSerializer;
use Magento\Webapi\Controller\Soap\Request as SoapRequest;
use Magento\Webapi\Exception as WebapiException;
use Magento\Webapi\Model\Soap\Config as SoapConfig;

/**
 * Handler of requests to SOAP server.
 *
 * The main responsibility is to instantiate proper action controller (service) and execute requested method on it.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Handler
{
    const RESULT_NODE_NAME = 'result';

    /** @var SoapRequest */
    protected $_request;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /** @var SoapConfig */
    protected $_apiConfig;

    /** @var AuthorizationInterface */
    protected $_authorization;

    /** @var SimpleDataObjectConverter */
    protected $_dataObjectConverter;

    /** @var ServiceArgsSerializer */
    protected $_serializer;

    /** @var DataObjectProcessor */
    protected $_dataObjectProcessor;

    /**
     * Initialize dependencies.
     *
     * @param SoapRequest $request
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param SoapConfig $apiConfig
     * @param AuthorizationInterface $authorization
     * @param SimpleDataObjectConverter $dataObjectConverter
     * @param ServiceArgsSerializer $serializer
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        SoapRequest $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SoapConfig $apiConfig,
        AuthorizationInterface $authorization,
        SimpleDataObjectConverter $dataObjectConverter,
        ServiceArgsSerializer $serializer,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->_apiConfig = $apiConfig;
        $this->_authorization = $authorization;
        $this->_dataObjectConverter = $dataObjectConverter;
        $this->_serializer = $serializer;
        $this->_dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Handler for all SOAP operations.
     *
     * @param string $operation
     * @param array $arguments
     * @return \stdClass|null
     * @throws WebapiException
     * @throws \LogicException
     * @throws AuthorizationException
     */
    public function __call($operation, $arguments)
    {
        $requestedServices = $this->_request->getRequestedServices();
        $serviceMethodInfo = $this->_apiConfig->getServiceMethodInfo($operation, $requestedServices);
        $serviceClass = $serviceMethodInfo[SoapConfig::KEY_CLASS];
        $serviceMethod = $serviceMethodInfo[SoapConfig::KEY_METHOD];

        // check if the operation is a secure operation & whether the request was made in HTTPS
        if ($serviceMethodInfo[SoapConfig::KEY_IS_SECURE] && !$this->_request->isSecure()) {
            throw new WebapiException(__("Operation allowed only in HTTPS"));
        }

        $isAllowed = false;
        foreach ($serviceMethodInfo[SoapConfig::KEY_ACL_RESOURCES] as $resource) {
            if ($this->_authorization->isAllowed($resource)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            // TODO: Consider passing Integration ID instead of Consumer ID
            throw new AuthorizationException(
                AuthorizationException::NOT_AUTHORIZED,
                ['resources' => implode(', ', $serviceMethodInfo[SoapConfig::KEY_ACL_RESOURCES])]
            );
        }
        $service = $this->_objectManager->get($serviceClass);
        $inputData = $this->_prepareRequestData($serviceClass, $serviceMethod, $arguments);
        $outputData = call_user_func_array([$service, $serviceMethod], $inputData);
        return $this->_prepareResponseData($outputData, $serviceClass, $serviceMethod);
    }

    /**
     * Convert SOAP operation arguments into format acceptable by service method.
     *
     * @param string $serviceClass
     * @param string $serviceMethod
     * @param array $arguments
     * @return array
     */
    protected function _prepareRequestData($serviceClass, $serviceMethod, $arguments)
    {
        /** SoapServer wraps parameters into array. Thus this wrapping should be removed to get access to parameters. */
        $arguments = reset($arguments);
        $arguments = $this->_dataObjectConverter->convertStdObjectToArray($arguments, true);
        return $this->_serializer->getInputData($serviceClass, $serviceMethod, $arguments);
    }

    /**
     * Convert service response into format acceptable by SoapServer.
     *
     * @param object|array|string|int|float|null $data
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _prepareResponseData($data, $serviceClassName, $serviceMethodName)
    {
        /** @var string $dataType */
        $dataType = $this->_dataObjectProcessor->getMethodReturnType($serviceClassName, $serviceMethodName);
        $result = null;
        if (is_object($data)) {
            $result = $this->_dataObjectConverter
                ->convertKeysToCamelCase($this->_dataObjectProcessor->buildOutputDataArray($data, $dataType));
        } elseif (is_array($data)) {
            $dataType = substr($dataType, 0, -2);
            foreach ($data as $key => $value) {
                if ($value instanceof ExtensibleDataInterface) {
                    $result[] = $this->_dataObjectConverter
                        ->convertKeysToCamelCase($this->_dataObjectProcessor->buildOutputDataArray($value, $dataType));
                } else {
                    $result[$key] = $value;
                }
            }
        } elseif (is_scalar($data) || is_null($data)) {
            $result = $data;
        } else {
            throw new \InvalidArgumentException("Service returned result in invalid format.");
        }
        return [self::RESULT_NODE_NAME => $result];
    }
}
