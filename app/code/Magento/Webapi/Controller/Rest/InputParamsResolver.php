<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Webapi\Authorization;
use Magento\Framework\Exception\AuthorizationException;

/**
 * This class is responsible for retrieving resolved input data
 */
class InputParamsResolver
{
    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @var ParamsOverrider
     */
    private $paramsOverrider;

    /**
     * @var ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Route
     */
    private $route;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param ParamsOverrider $paramsOverrider
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param Router $router
     * @param StoreManagerInterface $storeManager
     * @param Authorization $authorization
     */
    public function __construct(
        RestRequest $request,
        ParamsOverrider $paramsOverrider,
        ServiceInputProcessor $serviceInputProcessor,
        Router $router,
        StoreManagerInterface $storeManager,
        Authorization $authorization
    ) {
        $this->request = $request;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->router = $router;
        $this->storeManager = $storeManager;
        $this->authorization = $authorization;
    }

    /**
     * Process and resolve input parameters
     *
     * @return array
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function resolve()
    {
        $this->validateRequest();
        $route = $this->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        /*
         * Valid only for updates using PUT when passing id value both in URL and body
         */
        if ($this->request->getHttpMethod() == RestRequest::HTTP_METHOD_PUT) {
            $inputData = $this->paramsOverrider->overrideRequestBodyIdWithPathParam(
                $this->request->getParams(),
                $this->request->getBodyParams(),
                $serviceClassName,
                $serviceMethodName
            );
            $inputData = array_merge($inputData, $this->request->getParams());
        } else {
            $inputData = $this->request->getRequestData();
        }

        $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);
        return $inputParams;
    }

    /**
     * Retrieve current route.
     *
     * @return Route
     */
    public function getRoute()
    {
        if (!$this->route) {
            $this->route = $this->router->match($this->request);
        }
        return $this->route;
    }

    /**
     * Validate request
     *
     * @throws AuthorizationException
     * @throws \Magento\Framework\Webapi\Exception
     * @return void
     */
    private function validateRequest()
    {
        $this->checkPermissions();
        if ($this->getRoute()->isSecure() && !$this->request->isSecure()) {
            throw new \Magento\Framework\Webapi\Exception(__('Operation allowed only in HTTPS'));
        }
        if ($this->storeManager->getStore()->getCode() === Store::ADMIN_CODE
            && strtoupper($this->request->getMethod()) === RestRequest::HTTP_METHOD_GET
        ) {
            throw new \Magento\Framework\Webapi\Exception(__('Cannot perform GET operation with store code \'all\''));
        }
    }

    /**
     * Perform authentication and authorization.
     *
     * @throws \Magento\Framework\Exception\AuthorizationException
     * @return void
     */
    private function checkPermissions()
    {
        $route = $this->getRoute();
        if (!$this->authorization->isAllowed($route->getAclResources())) {
            $params = ['resources' => implode(', ', $route->getAclResources())];
            throw new AuthorizationException(
                __(AuthorizationException::NOT_AUTHORIZED, $params)
            );
        }
    }
}
