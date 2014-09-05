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
use Magento\Framework\Service\Data\AbstractSimpleObject;
use Magento\Framework\Service\Data\AbstractExtensibleObject;
use Magento\Framework\Service\ExtensibleDataObjectConverter;
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

    /** @var \Magento\Framework\View\LayoutInterface */
    protected $_layout;

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
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param RestResponse $response
     * @param Router $router
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param AuthorizationInterface $authorization
     * @param ServiceArgsSerializer $serializer
     * @param ErrorProcessor $errorProcessor
     * @param PathProcessor $pathProcessor
     * @param \Magento\Framework\App\AreaList $areaList
     * @param PartialResponseProcessor $partialResponseProcessor
     * @param UserContextInterface $userContext
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
        \Magento\Framework\View\LayoutInterface $layout,
        AuthorizationInterface $authorization,
        ServiceArgsSerializer $serializer,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList,
        PartialResponseProcessor $partialResponseProcessor,
        UserContextInterface $userContext
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_layout = $layout;
        $this->_authorization = $authorization;
        $this->_serializer = $serializer;
        $this->_errorProcessor = $errorProcessor;
        $this->_pathProcessor = $pathProcessor;
        $this->areaList = $areaList;
        $this->partialResponseProcessor = $partialResponseProcessor;
        $this->userContext = $userContext;
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
            if (!$this->_appState->isInstalled()) {
                throw new \Magento\Webapi\Exception(__('Magento is not yet installed'));
            }
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
            $outputData = $this->processServiceOutput($outputData);
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
     * Converts the incoming data into scalar or an array of scalars format.
     *
     * If the data provided is null, then an empty array is returned.  Otherwise, if the data is an object, it is
     * assumed to be a Data Object and converted to an associative array with keys representing the properties of the
     * Data Object.
     * Nested Data Objects are also converted.  If the data provided is itself an array, then we iterate through the
     * contents and convert each piece individually.
     *
     * @param mixed $data
     * @return array|int|string|bool|float Scalar or array of scalars
     */
    protected function processServiceOutput($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $datum) {
                if ($datum instanceof AbstractSimpleObject) {
                    $datum = $this->processDataObject($datum->__toArray());
                }
                $result[] = $datum;
            }
            return $result;
        } else if ($data instanceof AbstractSimpleObject) {
            return $this->processDataObject($data->__toArray());
        } else if (is_null($data)) {
            return [];
        } else {
            /** No processing is required for scalar types */
            return $data;
        }
    }

    /**
     * Convert data object to array and process available custom attributes
     *
     * @param array $dataObjectArray
     * @return array
     */
    protected function processDataObject($dataObjectArray)
    {
        if (isset($dataObjectArray[AbstractExtensibleObject::CUSTOM_ATTRIBUTES_KEY])) {
            $dataObjectArray = ExtensibleDataObjectConverter::convertCustomAttributesToSequentialArray(
                $dataObjectArray
            );
        }
        //Check for nested custom_attributes
        foreach ($dataObjectArray as $key => $value) {
            if (is_array($value)) {
                $dataObjectArray[$key] = $this->processDataObject($value);
            }
        }
        return $dataObjectArray;
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
