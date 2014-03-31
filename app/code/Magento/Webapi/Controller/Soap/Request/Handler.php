<?php
/**
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
namespace Magento\Webapi\Controller\Soap\Request;

use Magento\Authz\Service\AuthorizationV1Interface as AuthorizationService;
use Magento\Webapi\Model\Soap\Config as SoapConfig;
use Magento\Webapi\Controller\Soap\Request as SoapRequest;
use Magento\Webapi\Exception as WebapiException;
use Magento\Service\AuthorizationException;
use Magento\Webapi\Controller\ServiceArgsSerializer;

/**
 * Handler of requests to SOAP server.
 *
 * The main responsibility is to instantiate proper action controller (service) and execute requested method on it.
 *
 * TODO: Fix warnings suppression
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Handler
{
    const RESULT_NODE_NAME = 'result';

    /** @var SoapRequest */
    protected $_request;

    /** @var \Magento\ObjectManager */
    protected $_objectManager;

    /** @var SoapConfig */
    protected $_apiConfig;

    /** @var AuthorizationService */
    protected $_authorizationService;

    /** @var \Magento\Webapi\Helper\Data */
    protected $_helper;

    /** @var ServiceArgsSerializer */
    protected $_serializer;

    /**
     * Initialize dependencies.
     *
     * @param SoapRequest $request
     * @param \Magento\ObjectManager $objectManager
     * @param SoapConfig $apiConfig
     * @param AuthorizationService $authorizationService
     * @param \Magento\Webapi\Helper\Data $helper
     * @param ServiceArgsSerializer $serializer
     */
    public function __construct(
        SoapRequest $request,
        \Magento\ObjectManager $objectManager,
        SoapConfig $apiConfig,
        AuthorizationService $authorizationService,
        \Magento\Webapi\Helper\Data $helper,
        ServiceArgsSerializer $serializer
    ) {
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->_apiConfig = $apiConfig;
        $this->_authorizationService = $authorizationService;
        $this->_helper = $helper;
        $this->_serializer = $serializer;
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

        if (!$this->_authorizationService->isAllowed($serviceMethodInfo[SoapConfig::KEY_ACL_RESOURCES])) {
            // TODO: Consider passing Integration ID instead of Consumer ID
            throw new AuthorizationException(
                "Not Authorized.",
                0,
                null,
                array(),
                'authorization',
                "Consumer ID = {$this->_request->getConsumerId()}",
                implode($serviceMethodInfo[SoapConfig::KEY_ACL_RESOURCES], ', ')
            );
        }
        $service = $this->_objectManager->get($serviceClass);
        $inputData = $this->_prepareRequestData($serviceClass, $serviceMethod, $arguments);
        $outputData = call_user_func_array(array($service, $serviceMethod), $inputData);
        return $this->_prepareResponseData($outputData);
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
        $arguments = $this->_toArray($arguments);
        return $this->_serializer->getInputData($serviceClass, $serviceMethod, $arguments);
    }

    /**
     * Convert service response into format acceptable by SoapServer.
     *
     * @param object|array|string|int|float|null $data
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _prepareResponseData($data)
    {
        if ($this->_isDataObject($data)) {
            $result = $this->_unpackDataObject($data);
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $result[$key] = $this->_isDataObject($value) ? $this->_unpackDataObject($value) : $value;
            }
        } elseif (is_scalar($data) || is_null($data)) {
            $result = $data;
        } else {
            throw new \InvalidArgumentException("Service returned result in invalid format.");
        }
        return array(self::RESULT_NODE_NAME => $result);
    }

    /**
     * Create new object and initialize its public fields with data retrieved from Data Object.
     *
     * This method processes all nested Data Objects recursively.
     *
     * @param object $dataObject
     * @return \stdClass
     * @throws \InvalidArgumentException
     */
    protected function _unpackDataObject($dataObject)
    {
        if (!$this->_isDataObject($dataObject)) {
            throw new \InvalidArgumentException("Object is expected to implement __toArray() method.");
        }
        return $this->_unpackArray($dataObject->__toArray());
    }

    /**
     * @param array $dataArray
     * @return \stdClass
     */
    protected function _unpackArray($dataArray)
    {
        $response = new \stdClass();
        foreach ($dataArray as $fieldName => $fieldValue) {
            if ($this->_isDataObject($fieldValue)) {
                $fieldValue = $this->_unpackDataObject($fieldValue);
            }
            if (is_array($fieldValue)) {
                $fieldValue = $this->_unpackArray($fieldValue);
            }
            $fieldName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $fieldName))));
            $response->{$fieldName} = $fieldValue;
        }
        return $response;
    }

    /**
     * Check if provided variable is service Data Object.
     *
     * @param mixed $var
     * @return bool
     */
    protected function _isDataObject($var)
    {
        return is_object($var) && method_exists($var, '__toArray');
    }

    /**
     * Convert multidimensional object/array into multidimensional array of primitives.
     *
     * @param object|array $input
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _toArray($input)
    {
        if (!is_object($input) && !is_array($input)) {
            throw new \InvalidArgumentException("Input argument must be an array or object");
        }
        $result = array();
        foreach ((array)$input as $key => $value) {
            if (is_object($value) || is_array($value)) {
                $result[$key] = $this->_toArray($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
