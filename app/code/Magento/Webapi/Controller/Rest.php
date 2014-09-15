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

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Service\SimpleDataObjectConverter;
use Magento\Webapi\Controller\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Response as RestResponse;
use Magento\Webapi\Controller\Rest\Response\PartialResponseProcessor;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Config\Converter;
use Magento\Webapi\Model\PathProcessor;

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
    /** @var Router */
    protected $_router;

    /** @var Route */
    protected $_route;

    /** @var RestRequest */
    protected $_request;

    /** @var RestResponse */
    protected $_response;

    /** @var \Magento\Framework\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Framework\App\State */
    protected $_appState;

    /** @var AuthorizationInterface */
    protected $_authorization;

    /** @var ServiceArgsSerializer */
    protected $_serializer;

    /** @var ErrorProcessor */
    protected $_errorProcessor;

    /** @var PathProcessor */
    protected $_pathProcessor;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * @var PartialResponseProcessor
     */
    protected $partialResponseProcessor;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $session;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * @var SimpleDataObjectConverter $dataObjectConverter
     */
    protected $dataObjectConverter;

    /**
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param RestResponse $response
     * @param Router $router
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\State $appState
     * @param AuthorizationInterface $authorization
     * @param ServiceArgsSerializer $serializer
     * @param ErrorProcessor $errorProcessor
     * @param PathProcessor $pathProcessor
     * @param \Magento\Framework\App\AreaList $areaList
     * @param PartialResponseProcessor $partialResponseProcessor
     * @param UserContextInterface $userContext
     * @param SimpleDataObjectConverter $dataObjectConverter
     *
     * TODO: Consider removal of warning suppression
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RestRequest $request,
        RestResponse $response,
        Router $router,
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\App\State $appState,
        AuthorizationInterface $authorization,
        ServiceArgsSerializer $serializer,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList,
        PartialResponseProcessor $partialResponseProcessor,
        UserContextInterface $userContext,
        SimpleDataObjectConverter $dataObjectConverter
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_authorization = $authorization;
        $this->_serializer = $serializer;
        $this->_errorProcessor = $errorProcessor;
        $this->_pathProcessor = $pathProcessor;
        $this->areaList = $areaList;
        $this->partialResponseProcessor = $partialResponseProcessor;
        $this->userContext = $userContext;
        $this->dataObjectConverter = $dataObjectConverter;
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
            $this->checkPermissions();
            $route = $this->getCurrentRoute();
            if ($route->isSecure() && !$this->_request->isSecure()) {
                throw new \Magento\Webapi\Exception(__('Operation allowed only in HTTPS'));
            }
            /** @var array $inputData */
            $inputData = $this->_request->getRequestData();
            $serviceMethodName = $route->getServiceMethod();
            $serviceClassName = $route->getServiceClass();
            $inputData = $this->overrideParams($inputData, $route->getParameters());
            $inputParams = $this->_serializer->getInputData($serviceClassName, $serviceMethodName, $inputData);
            $service = $this->_objectManager->get($serviceClassName);
            /** @var \Magento\Framework\Service\Data\AbstractExtensibleObject $outputData */
            $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
            $outputData = $this->dataObjectConverter->processServiceOutput($outputData);
            if ($this->_request->getParam(PartialResponseProcessor::FILTER_PARAMETER) && is_array($outputData)) {
                $outputData = $this->partialResponseProcessor->filter($outputData);
            }
            $this->_response->prepareResponse($outputData);
        } catch (\Exception $e) {
            $maskedException = $this->_errorProcessor->maskException($e);
            $this->_response->setException($maskedException);
        }
        return $this->_response;
    }

    /**
     * Override parameter values based on webapi.xml
     *
     * @param array $inputData Incoming data from request
     * @param array $parameters Contains parameters to replace or default
     * @return array Data in same format as $inputData with appropriate parameters added or changed
     */
    protected function overrideParams(array $inputData, array $parameters)
    {
        foreach ($parameters as $name => $paramData) {
            if ($paramData[Converter::KEY_FORCE] || !isset($inputData[$name])) {
                if ($paramData[Converter::KEY_VALUE] == '%customer_id%'
                    && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
                ) {
                    $value = $this->userContext->getUserId();
                } else {
                    $value = $paramData[Converter::KEY_VALUE];
                }
                $inputData[$name] = $value;
            }
        }
        return $inputData;
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
            throw new AuthorizationException(AuthorizationException::NOT_AUTHORIZED, $params);
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
}
