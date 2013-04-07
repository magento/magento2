<?php
/**
 * Dispatcher for REST API calls.
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
class Mage_Webapi_Controller_Dispatcher_Rest implements  Mage_Webapi_Controller_DispatcherInterface
{
    /** @var Mage_Webapi_Model_Config_Rest */
    protected $_apiConfig;

    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Presentation */
    protected $_restPresentation;

    /** @var Mage_Webapi_Controller_Router_Rest */
    protected $_router;

    /** @var Mage_Webapi_Controller_Dispatcher_Rest_Authentication */
    protected $_authentication;

    /** @var Mage_Webapi_Controller_Request_Rest */
    protected $_request;

    /**
     * Action controller factory.
     *
     * @var Mage_Webapi_Controller_Action_Factory
     */
    protected $_controllerFactory;

    /** @var Mage_Webapi_Model_Authorization */
    protected $_authorization;

    /** @var Mage_Webapi_Controller_Response_Rest */
    protected $_response;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Webapi_Model_Config_Rest $apiConfig
     * @param Mage_Webapi_Controller_Request_Rest $request
     * @param Mage_Webapi_Controller_Response_Rest $response
     * @param Mage_Webapi_Controller_Action_Factory $controllerFactory
     * @param Mage_Webapi_Controller_Dispatcher_Rest_Presentation $restPresentation
     * @param Mage_Webapi_Controller_Router_Rest $router
     * @param Mage_Webapi_Model_Authorization $authorization
     * @param Mage_Webapi_Controller_Dispatcher_Rest_Authentication $authentication
     */
    public function __construct(
        Mage_Webapi_Model_Config_Rest $apiConfig,
        Mage_Webapi_Controller_Request_Rest $request,
        Mage_Webapi_Controller_Response_Rest $response,
        Mage_Webapi_Controller_Action_Factory $controllerFactory,
        Mage_Webapi_Controller_Dispatcher_Rest_Presentation $restPresentation,
        Mage_Webapi_Controller_Router_Rest $router,
        Mage_Webapi_Model_Authorization $authorization,
        Mage_Webapi_Controller_Dispatcher_Rest_Authentication $authentication
    ) {
        $this->_apiConfig = $apiConfig;
        $this->_restPresentation = $restPresentation;
        $this->_router = $router;
        $this->_authentication = $authentication;
        $this->_request = $request;
        $this->_controllerFactory = $controllerFactory;
        $this->_authorization = $authorization;
        $this->_response = $response;
    }

    /**
     * Handle REST request.
     *
     * @return Mage_Webapi_Controller_Dispatcher_Rest
     */
    public function dispatch()
    {
        try {
            $this->_authentication->authenticate();
            $route = $this->_router->match($this->_request);

            $operation = $this->_request->getOperationName();
            $resourceVersion = $this->_request->getResourceVersion();
            $this->_apiConfig->validateVersionNumber($resourceVersion, $this->_request->getResourceName());
            $method = $this->_apiConfig->getMethodNameByOperation($operation, $resourceVersion);
            $controllerClassName = $this->_apiConfig->getControllerClassByOperationName($operation);
            $controllerInstance = $this->_controllerFactory->createActionController(
                $controllerClassName,
                $this->_request
            );
            $versionAfterFallback = $this->_apiConfig->identifyVersionSuffix(
                $operation,
                $resourceVersion,
                $controllerInstance
            );
            /**
             * Route check has two stages:
             * The first is performed against full list of routes that is merged from all resources.
             * The second stage of route check can be performed only when actual version to be executed is known.
             */
            $this->_router->checkRoute($this->_request, $method, $versionAfterFallback);
            $this->_apiConfig->checkDeprecationPolicy($route->getResourceName(), $method, $versionAfterFallback);
            $action = $method . $versionAfterFallback;

            $this->_authorization->checkResourceAcl($route->getResourceName(), $method);

            $inputData = $this->_restPresentation->fetchRequestData($controllerInstance, $action);
            $outputData = call_user_func_array(array($controllerInstance, $action), $inputData);
            $this->_restPresentation->prepareResponse($method, $outputData);
        } catch (Exception $e) {
            $this->_response->setException($e);
        }
        $this->_response->sendResponse();
        return $this;
    }
}
