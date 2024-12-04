<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\Rest\RequestValidatorInterface;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\RequestProcessorPool;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;

/**
 * Front controller for WebAPI REST area.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Rest implements \Magento\Framework\App\FrontControllerInterface
{
    /**
     * Path for accessing REST API schema
     *
     * @deprecated 100.3.0
     */
    public const SCHEMA_PATH = '/schema';

    /**
     * @var Router
     * @deprecated 100.1.0
     * @see MAGETWO-71174
     */
    protected $_router;

    /**
     * @var Route
     * @deprecated 100.1.0
     * @see MAGETWO-71174
     */
    protected $_route;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response
     */
    protected $_response;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var Authorization
     * @deprecated 100.1.0
     * @see MAGETWO-71174
     */
    protected $authorization;

    /**
     * @var ServiceInputProcessor
     * @deprecated 100.1.0
     * @see MAGETWO-71174
     */
    protected $serviceInputProcessor;

    /**
     * @var \Magento\Framework\Webapi\ErrorProcessor
     */
    protected $_errorProcessor;

    /**
     * @var \Magento\Webapi\Controller\PathProcessor
     */
    protected $_pathProcessor;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var ParamsOverrider
     * @deprecated 100.1.0
     * @see MAGETWO-71174
     */
    protected $paramsOverrider;

    /**
     * @var RequestProcessorPool
     */
    protected $requestProcessorPool;

    /**
     * @var RequestValidatorInterface
     */
    private $requestValidator;

    /**
     * @var StoreManagerInterface
     * @deprecated 100.1.0
     * @see MAGETWO-71174
     */
    private $storeManager;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param RestResponse $response
     * @param Router $router
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $appState
     * @param Authorization $authorization
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param ErrorProcessor $errorProcessor
     * @param PathProcessor $pathProcessor
     * @param \Magento\Framework\App\AreaList $areaList
     * @param ParamsOverrider $paramsOverrider
     * @param StoreManagerInterface $storeManager
     * @param RequestProcessorPool $requestProcessorPool
     * @param RequestValidatorInterface $requestValidator
     *
     * TODO: Consider removal of warning suppression
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RestRequest $request,
        RestResponse $response,
        Router $router,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $appState,
        Authorization $authorization,
        ServiceInputProcessor $serviceInputProcessor,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList,
        ParamsOverrider $paramsOverrider,
        StoreManagerInterface $storeManager,
        RequestProcessorPool $requestProcessorPool,
        RequestValidatorInterface $requestValidator
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->authorization = $authorization;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->_errorProcessor = $errorProcessor;
        $this->_pathProcessor = $pathProcessor;
        $this->areaList = $areaList;
        $this->paramsOverrider = $paramsOverrider;
        $this->storeManager = $storeManager;
        $this->requestProcessorPool = $requestProcessorPool;
        $this->requestValidator = $requestValidator;
    }

    /**
     * Handle REST request
     *
     * Based on request decide is it schema request or API request and process accordingly.
     * Throws Exception in case if cannot be processed properly.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $path = $this->_pathProcessor->process($request->getPathInfo());
        $this->_request->setPathInfo($path);
        $this->areaList->getArea($this->_appState->getAreaCode())
            ->load(\Magento\Framework\App\Area::PART_TRANSLATE);
        try {
            $this->requestValidator->validate($this->_request);
            $processor = $this->requestProcessorPool->getProcessor($this->_request);
            $processor->process($this->_request);
        } catch (\Exception $e) {
            $maskedException = $this->_errorProcessor->maskException($e);
            $this->_response->setException($maskedException);
        }

        return $this->_response;
    }

    /**
     * Check if current request is schema request.
     *
     * @return bool
     */
    protected function isSchemaRequest()
    {
        return $this->_request->getPathInfo() === self::SCHEMA_PATH;
    }

    /**
     * Retrieve current route.
     *
     * @return Route
     * @deprecated 100.1.0
     * @see \Magento\Webapi\Controller\Rest\InputParamsResolver::getRoute
     */
    protected function getCurrentRoute()
    {
        if (!$this->_route) {
            $this->_route = $this->_router->match($this->_request);
        }

        return $this->_route;
    }

    /**
     * Perform authentication and authorization.
     *
     * @throws \Magento\Framework\Exception\AuthorizationException
     * @return void
     * @deprecated 100.1.0
     * @see \Magento\Webapi\Controller\Rest\RequestValidator::checkPermissions
     */
    protected function checkPermissions()
    {
        $route = $this->getCurrentRoute();
        if (!$this->authorization->isAllowed($route->getAclResources())) {
            $params = ['resources' => implode(', ', $route->getAclResources())];
            throw new AuthorizationException(
                __("The consumer isn't authorized to access %resources.", $params)
            );
        }
    }

    /**
     * Validate request
     *
     * @throws AuthorizationException
     * @throws \Magento\Framework\Webapi\Exception
     * @return void
     * @deprecated 100.1.0
     * @see \Magento\Webapi\Controller\Rest\RequestValidator::validate
     */
    protected function validateRequest()
    {
        $this->checkPermissions();
        if ($this->getCurrentRoute()->isSecure() && !$this->_request->isSecure()) {
            throw new \Magento\Framework\Webapi\Exception(__('Operation allowed only in HTTPS'));
        }
        if ($this->storeManager->getStore()->getCode() === Store::ADMIN_CODE
            && strtoupper($this->_request->getMethod()) === RestRequest::HTTP_METHOD_GET
        ) {
            throw
            new \Magento\Framework\Webapi\Exception(__('Cannot perform GET operation with store code \'all\''));
        }
    }
}
