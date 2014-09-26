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

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Service\Data\AbstractSimpleObject;
use Magento\Framework\Service\SimpleDataObjectConverter;
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

    /** @var \Magento\Framework\ObjectManager */
    protected $_objectManager;

    /** @var SoapConfig */
    protected $_apiConfig;

    /** @var AuthorizationInterface */
    protected $_authorization;

    /** @var SimpleDataObjectConverter */
    protected $_dataObjectConverter;

    /** @var ServiceArgsSerializer */
    protected $_serializer;

    /**
     * Initialize dependencies.
     *
     * @param SoapRequest $request
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param SoapConfig $apiConfig
     * @param AuthorizationInterface $authorization
     * @param SimpleDataObjectConverter $dataObjectConverter
     * @param ServiceArgsSerializer $serializer
     */
    public function __construct(
        SoapRequest $request,
        \Magento\Framework\ObjectManager $objectManager,
        SoapConfig $apiConfig,
        AuthorizationInterface $authorization,
        SimpleDataObjectConverter $dataObjectConverter,
        ServiceArgsSerializer $serializer
    ) {
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->_apiConfig = $apiConfig;
        $this->_authorization = $authorization;
        $this->_dataObjectConverter = $dataObjectConverter;
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
        $arguments = $this->_dataObjectConverter->convertStdObjectToArray($arguments, true);
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
        $result = null;
        if ($data instanceof AbstractSimpleObject) {
            $result = $this->_dataObjectConverter->convertKeysToCamelCase($data->__toArray());
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($value instanceof AbstractSimpleObject) {
                    $result[] = $this->_dataObjectConverter->convertKeysToCamelCase($value->__toArray());
                } else {
                    $result[$key] = $value;
                }
            }
        } elseif (is_scalar($data) || is_null($data)) {
            $result = $data;
        } else {
            throw new \InvalidArgumentException("Service returned result in invalid format.");
        }
        return array(self::RESULT_NODE_NAME => $result);
    }
}
