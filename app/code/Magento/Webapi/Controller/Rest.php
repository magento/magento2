<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Exception as WebapiException;
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
class Rest implements FrontControllerInterface
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
     * @var RestRequest
     */
    protected $_request;

    /**
     * @var RestResponse
     */
    protected $_response;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var ErrorProcessor
     */
    protected $_errorProcessor;

    /**
     * @var PathProcessor
     */
    protected $_pathProcessor;

    /**
     * @var Generic
     */
    protected $session;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param RestResponse $response
     * @param Router $router
     * @param ObjectManagerInterface $objectManager
     * @param State $appState
     * @param Authorization $authorization
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param ErrorProcessor $errorProcessor
     * @param PathProcessor $pathProcessor
     * @param AreaList $areaList
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
        ObjectManagerInterface $objectManager,
        State $appState,
        protected readonly Authorization $authorization,
        protected readonly ServiceInputProcessor $serviceInputProcessor,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        protected readonly AreaList $areaList,
        protected readonly ParamsOverrider $paramsOverrider,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly RequestProcessorPool $requestProcessorPool,
        private readonly RequestValidatorInterface $requestValidator
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_errorProcessor = $errorProcessor;
        $this->_pathProcessor = $pathProcessor;
    }

    /**
     * Handle REST request
     *
     * Based on request decide is it schema request or API request and process accordingly.
     * Throws Exception in case if cannot be processed properly.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $path = $this->_pathProcessor->process($request->getPathInfo());
        $this->_request->setPathInfo($path);
        $this->areaList->getArea($this->_appState->getAreaCode())
            ->load(Area::PART_TRANSLATE);
        try {
            $this->requestValidator->validate($this->_request);
            $processor = $this->requestProcessorPool->getProcessor($this->_request);
            $processor->process($this->_request);
        } catch (CouldNotSaveException $e) {
            $maskedException = $this->_errorProcessor->maskException($e);
            $this->_response->setException($maskedException);
            $this->_response->setHeader('errorRedirectAction', '#shipping');
        } catch (Exception $e) {
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
     * @throws AuthorizationException
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
     * @return void
     * @throws WebapiException
     * @throws AuthorizationException
     * @deprecated 100.1.0
     * @see \Magento\Webapi\Controller\Rest\RequestValidator::validate
     */
    protected function validateRequest()
    {
        $this->checkPermissions();
        if ($this->getCurrentRoute()->isSecure() && !$this->_request->isSecure()) {
            throw new WebapiException(__('Operation allowed only in HTTPS'));
        }
        if ($this->storeManager->getStore()->getCode() === Store::ADMIN_CODE
            && strtoupper($this->_request->getMethod()) === RestRequest::HTTP_METHOD_GET
        ) {
            throw new WebapiException(__('Cannot perform GET operation with store code \'all\''));
        }
    }
}
