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

namespace Magento\Webapi\Controller;

use Magento\Authz\Service\AuthorizationV1Interface as AuthorizationService;

/**
 * Front controller for WebAPI REST area.
 *
 * TODO: Fix coupling between objects
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rest implements \Magento\App\FrontControllerInterface
{
    /** @var \Magento\Webapi\Controller\Rest\Router */
    protected $_router;

    /** @var \Magento\Webapi\Controller\Rest\Request */
    protected $_request;

    /** @var \Magento\Webapi\Controller\Rest\Response */
    protected $_response;

    /** @var \Magento\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\App\State */
    protected $_appState;

    /** @var \Magento\Oauth\OauthInterface */
    protected $_oauthService;

    /** @var  \Magento\Oauth\Helper\Request */
    protected $_oauthHelper;

    /** @var AuthorizationService */
    protected $_authorizationService;

    /**
     * @param Rest\Request $request
     * @param Rest\Response $response
     * @param Rest\Router $router
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\State $appState
     * @param \Magento\Oauth\OauthInterface $oauthService
     * @param \Magento\Oauth\Helper\Request $oauthHelper
     * @param AuthorizationService $authorizationService
     */
    public function __construct(
        \Magento\Webapi\Controller\Rest\Request $request,
        \Magento\Webapi\Controller\Rest\Response $response,
        \Magento\Webapi\Controller\Rest\Router $router,
        \Magento\ObjectManager $objectManager,
        \Magento\App\State $appState,
        \Magento\Oauth\OauthInterface $oauthService,
        \Magento\Oauth\Helper\Request $oauthHelper,
        AuthorizationService $authorizationService
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_oauthService = $oauthService;
        $this->_oauthHelper = $oauthHelper;
        $this->_authorizationService = $authorizationService;
    }

    /**
     * Initialize front controller
     *
     * @return \Magento\Webapi\Controller\Rest
     */
    public function init()
    {
        return $this;
    }

    /**
     * Handle REST request
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(\Magento\App\RequestInterface $request)
    {
        $pathParts = explode('/', trim($request->getPathInfo(), '/'));
        array_shift($pathParts);
        $request->setPathInfo('/' . implode('/', $pathParts));
        try {
            if (!$this->_appState->isInstalled()) {
                throw new \Magento\Webapi\Exception(__('Magento is not yet installed'));
            }
            // TODO: Consider changing service interface to operate with objects to avoid overhead
            $requestUrl = $this->_oauthHelper->getRequestUrl($this->_request);
            $oauthRequest = $this->_oauthHelper->prepareRequest(
                $this->_request, $requestUrl, $this->_request->getRequestData()
            );
            $consumerId = $this->_oauthService->validateAccessTokenRequest(
                $oauthRequest, $requestUrl, $this->_request->getMethod()
            );
            $this->_request->setConsumerId($consumerId);

            $route = $this->_router->match($this->_request);

            if (!$this->_authorizationService->isAllowed($route->getAclResources())) {
                // TODO: Consider passing Integration ID instead of Consumer ID
                throw new \Magento\Service\AuthorizationException(
                    "Not Authorized.",
                    0,
                    null,
                    array(),
                    'authorization',
                    "Consumer ID = {$consumerId}",
                    implode($route->getAclResources(), ', '));
            }

            if ($route->isSecure() && !$this->_request->isSecure()) {
                throw new \Magento\Webapi\Exception(__('Operation allowed only in HTTPS'));
            }
            /** @var array $inputData */
            $inputData = $this->_request->getRequestData();
            $serviceMethod = $route->getServiceMethod();
            $service = $this->_objectManager->get($route->getServiceClass());
            $outputData = $service->$serviceMethod($inputData);
            if (!is_array($outputData)) {
                throw new \LogicException(
                    sprintf('The method "%s" of service "%s" must return an array.', $serviceMethod,
                        $route->getServiceClass())
                );
            }
            $this->_response->prepareResponse($outputData);
        } catch (\Exception $e) {
            $this->_response->setException($e);
        }
        return $this->_response;
    }
}
