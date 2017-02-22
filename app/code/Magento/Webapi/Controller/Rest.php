<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Controller;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Webapi\ErrorProcessor;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Framework\Webapi\Rest\Response\FieldsFilter;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Webapi\Controller\Rest\ParamsOverrider;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Rest\Swagger\Generator;

/**
 * Front controller for WebAPI REST area.
 *
 * TODO: Consider warnings suppression removal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Rest implements \Magento\Framework\App\FrontControllerInterface
{
    /** Path for accessing REST API schema */
    const SCHEMA_PATH = '/schema';

    /** @var Router */
    protected $_router;

    /** @var Route */
    protected $_route;

    /** @var RestRequest */
    protected $_request;

    /** @var RestResponse */
    protected $_response;

    /** @var \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    /** @var \Magento\Framework\App\State */
    protected $_appState;

    /** @var AuthorizationInterface */
    protected $_authorization;

    /** @var ServiceInputProcessor */
    protected $serviceInputProcessor;

    /** @var ErrorProcessor */
    protected $_errorProcessor;

    /** @var PathProcessor */
    protected $_pathProcessor;

    /** @var \Magento\Framework\App\AreaList */
    protected $areaList;

    /** @var FieldsFilter */
    protected $fieldsFilter;

    /** @var \Magento\Framework\Session\Generic */
    protected $session;

    /** @var ParamsOverrider */
    protected $paramsOverrider;

    /** @var ServiceOutputProcessor $serviceOutputProcessor */
    protected $serviceOutputProcessor;

    /** @var Generator */
    protected $swaggerGenerator;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param RestResponse $response
     * @param Router $router
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $appState
     * @param AuthorizationInterface $authorization
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param ErrorProcessor $errorProcessor
     * @param PathProcessor $pathProcessor
     * @param \Magento\Framework\App\AreaList $areaList
     * @param FieldsFilter $fieldsFilter
     * @param ParamsOverrider $paramsOverrider
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param Generator $swaggerGenerator ,
     * @param StoreManagerInterface $storeManager
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
        AuthorizationInterface $authorization,
        ServiceInputProcessor $serviceInputProcessor,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList,
        FieldsFilter $fieldsFilter,
        ParamsOverrider $paramsOverrider,
        ServiceOutputProcessor $serviceOutputProcessor,
        Generator $swaggerGenerator,
        StoreManagerInterface $storeManager
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_authorization = $authorization;
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->_errorProcessor = $errorProcessor;
        $this->_pathProcessor = $pathProcessor;
        $this->areaList = $areaList;
        $this->fieldsFilter = $fieldsFilter;
        $this->paramsOverrider = $paramsOverrider;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->swaggerGenerator = $swaggerGenerator;
        $this->storeManager = $storeManager;
    }

    /**
     * Handle REST request
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
            if ($this->isSchemaRequest()) {
                $this->processSchemaRequest();
            } else {
                $this->processApiRequest();
            }
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
     */
    protected function checkPermissions()
    {
        $route = $this->getCurrentRoute();
        if (!$this->isAllowed($route->getAclResources())) {
            $params = ['resources' => implode(', ', $route->getAclResources())];
            throw new AuthorizationException(
                __(AuthorizationException::NOT_AUTHORIZED, $params)
            );
        }
    }

    /**
     * Check if all ACL resources are allowed to be accessed by current API user.
     *
     * @param string[] $aclResources
     * @return bool
     */
    protected function isAllowed($aclResources)
    {
        foreach ($aclResources as $resource) {
            if (!$this->_authorization->isAllowed($resource)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Execute schema request
     *
     * @return void
     */
    protected function processSchemaRequest()
    {
        $requestedServices = $this->_request->getRequestedServices('all');
        $requestedServices = $requestedServices == Request::ALL_SERVICES
            ? array_keys($this->swaggerGenerator->getListOfServices())
            : $requestedServices;
        $responseBody = $this->swaggerGenerator->generate(
            $requestedServices,
            $this->_request->getScheme(),
            $this->_request->getHttpHost(),
            $this->_request->getRequestUri()
        );
        $this->_response->setBody($responseBody)->setHeader('Content-Type', 'application/json');
    }

    /**
     * Execute API request
     *
     * @return void
     * @throws AuthorizationException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Webapi\Exception
     */
    protected function processApiRequest()
    {
        $this->validateRequest();
        /** @var array $inputData */
        $inputData = $this->_request->getRequestData();
        $route = $this->getCurrentRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();
        $inputData = $this->paramsOverrider->override($inputData, $route->getParameters());
        $inputParams = $this->serviceInputProcessor->process($serviceClassName, $serviceMethodName, $inputData);
        $service = $this->_objectManager->get($serviceClassName);
        /** @var \Magento\Framework\Api\AbstractExtensibleObject $outputData */
        $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
        $outputData = $this->serviceOutputProcessor->process(
            $outputData,
            $serviceClassName,
            $serviceMethodName
        );
        if ($this->_request->getParam(FieldsFilter::FILTER_PARAMETER) && is_array($outputData)) {
            $outputData = $this->fieldsFilter->filter($outputData);
        }
        $this->_response->prepareResponse($outputData);
    }

    /**
     * Validate request
     *
     * @throws AuthorizationException
     * @throws \Magento\Framework\Webapi\Exception
     * @return void
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
            throw new \Magento\Framework\Webapi\Exception(__('Cannot perform GET operation with store code \'all\''));
        }
    }
}
