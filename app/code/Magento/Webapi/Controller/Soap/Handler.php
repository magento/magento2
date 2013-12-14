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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Webapi\Controller\Soap;

use Magento\Authz\Service\AuthorizationV1Interface as AuthorizationService;
use Magento\Webapi\Model\Soap\Config as SoapConfig;
use Magento\Webapi\Controller\Soap\Request as SoapRequest;
use Magento\Webapi\Exception as WebapiException;
use Magento\Service\AuthorizationException;

/**
 * Handler of requests to SOAP server.
 *
 * The main responsibility is to instantiate proper action controller (service) and execute requested method on it.
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

    /**
     * Initialize dependencies.
     *
     * @param SoapRequest $request
     * @param \Magento\ObjectManager $objectManager
     * @param SoapConfig $apiConfig
     * @param AuthorizationService $authorizationService
     */
    public function __construct(
        SoapRequest $request,
        \Magento\ObjectManager $objectManager,
        SoapConfig $apiConfig,
        AuthorizationService $authorizationService
    ) {
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->_apiConfig = $apiConfig;
        $this->_authorizationService = $authorizationService;
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
                implode($serviceMethodInfo[SoapConfig::KEY_ACL_RESOURCES], ', '));
        }
        $service = $this->_objectManager->get($serviceClass);
        $outputData = $service->$serviceMethod($this->_prepareParameters($arguments));
        if (!is_array($outputData)) {
            throw new \LogicException(
                sprintf('The method "%s" of service "%s" must return an array.', $serviceMethod, $serviceClass)
            );
        }
        return $outputData;
    }

    /**
     * Extract service method parameters from SOAP operation arguments.
     *
     * @param \stdClass|array $arguments
     * @return array
     */
    protected function _prepareParameters($arguments)
    {
        /** SoapServer wraps parameters into array. Thus this wrapping should be removed to get access to parameters. */
        $arguments = reset($arguments);
        $this->_associativeObjectToArray($arguments);
        $arguments = get_object_vars($arguments);
        return $arguments;
    }

    /**
     * Go through an object parameters and unpack associative object to array.
     *
     * This function uses recursion and operates by reference.
     *
     * @param \stdClass|array $obj
     * @return bool
     */
    protected function _associativeObjectToArray(&$obj)
    {
        if (is_object($obj)) {
            if (property_exists($obj, 'key') && property_exists($obj, 'value')) {
                if (count(array_keys(get_object_vars($obj))) === 2) {
                    $obj = array($obj->key => $obj->value);
                    return true;
                }
            } else {
                foreach (array_keys(get_object_vars($obj)) as $key) {
                    $this->_associativeObjectToArray($obj->$key);
                }
            }
        } else if (is_array($obj)) {
            $arr = array();
            $object = $obj;
            foreach ($obj as &$value) {
                if ($this->_associativeObjectToArray($value)) {
                    array_walk($value, function ($val, $key) use (&$arr) {
                        $arr[$key] = $val;
                    });
                    $object = $arr;
                }
            }
            $obj = $object;
        }
        return false;
    }
}
