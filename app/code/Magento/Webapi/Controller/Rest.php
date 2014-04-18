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
use Magento\Service\Data\AbstractObject;
use Magento\Webapi\Controller\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Response as RestResponse;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Model\PathProcessor;
use Magento\Webapi\Model\Config\Converter;

/**
 * Front controller for WebAPI REST area.
 *
 * TODO: Consider warnings suppression removal
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Rest implements \Magento\Framework\App\FrontControllerInterface
{
    /** @var Router */
    protected $_router;

    /** @var RestRequest */
    protected $_request;

    /** @var RestResponse */
    protected $_response;

    /** @var \Magento\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Framework\App\State */
    protected $_appState;

    /** @var \Magento\View\LayoutInterface */
    protected $_layout;

    /** @var \Magento\Oauth\OauthInterface */
    protected $_oauthService;

    /** @var  \Magento\Oauth\Helper\Request */
    protected $_oauthHelper;

    /** @var AuthorizationService */
    protected $_authorizationService;

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
     * Initialize dependencies
     *
     * @param RestRequest $request
     * @param RestResponse $response
     * @param Router $router
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Oauth\OauthInterface $oauthService
     * @param \Magento\Oauth\Helper\Request $oauthHelper
     * @param AuthorizationService $authorizationService
     * @param ServiceArgsSerializer $serializer
     * @param ErrorProcessor $errorProcessor
     * @param PathProcessor $pathProcessor
     * @param \Magento\Framework\App\AreaList $areaList
     *
     * TODO: Consider removal of warning suppression
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RestRequest $request,
        RestResponse $response,
        Router $router,
        \Magento\ObjectManager $objectManager,
        \Magento\Framework\App\State $appState,
        \Magento\View\LayoutInterface $layout,
        \Magento\Oauth\OauthInterface $oauthService,
        \Magento\Oauth\Helper\Request $oauthHelper,
        AuthorizationService $authorizationService,
        ServiceArgsSerializer $serializer,
        ErrorProcessor $errorProcessor,
        PathProcessor $pathProcessor,
        \Magento\Framework\App\AreaList $areaList
    ) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_response = $response;
        $this->_objectManager = $objectManager;
        $this->_appState = $appState;
        $this->_layout = $layout;
        $this->_oauthService = $oauthService;
        $this->_oauthHelper = $oauthHelper;
        $this->_authorizationService = $authorizationService;
        $this->_serializer = $serializer;
        $this->_errorProcessor = $errorProcessor;
        $this->_pathProcessor = $pathProcessor;
        $this->areaList = $areaList;
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
            ->load(\Magento\Core\Model\App\Area::PART_TRANSLATE);
        try {
            if (!$this->_appState->isInstalled()) {
                throw new \Magento\Webapi\Exception(__('Magento is not yet installed'));
            }
            $oauthRequest = $this->_oauthHelper->prepareRequest($this->_request);
            $consumerId = $this->_oauthService->validateAccessTokenRequest(
                $oauthRequest,
                $this->_oauthHelper->getRequestUrl($this->_request),
                $this->_request->getMethod()
            );
            $this->_request->setConsumerId($consumerId);
            $route = $this->_router->match($this->_request);

            if (!$this->_authorizationService->isAllowed($route->getAclResources())) {
                // TODO: Consider passing Integration ID instead of Consumer ID
                throw new \Magento\Webapi\ServiceAuthorizationException(
                    "Not Authorized.",
                    0,
                    null,
                    array(),
                    'authorization',
                    "Consumer ID = {$consumerId}",
                    implode($route->getAclResources(), ', ')
                );
            }

            if ($route->isSecure() && !$this->_request->isSecure()) {
                throw new \Magento\Webapi\Exception(__('Operation allowed only in HTTPS'));
            }
            /** @var array $inputData */
            $inputData = $this->_request->getRequestData();
            $serviceMethodName = $route->getServiceMethod();
            $serviceClassName = $route->getServiceClass();
            $inputData = $this->_overrideParams($inputData, $route->getParameters());
            $inputParams = $this->_serializer->getInputData($serviceClassName, $serviceMethodName, $inputData);
            $service = $this->_objectManager->get($serviceClassName);
            /** @var \Magento\Service\Data\AbstractObject $outputData */
            $outputData = call_user_func_array([$service, $serviceMethodName], $inputParams);
            $outputArray = $this->_processServiceOutput($outputData);
            $this->_response->prepareResponse($outputArray);
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
    protected function _processServiceOutput($data)
    {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $datum) {
                if ($datum instanceof AbstractObject) {
                    $result[] = $datum->__toArray();
                } else {
                    $result[] = $datum;
                }
            }
        } else if ($data instanceof AbstractObject) {
            $result = $data->__toArray();
        } else if (is_null($data)) {
            $result = [];
        } else {
            /** No processing is required for scalar types */
            $result = $data;
        }
        return $result;
    }

    /**
     * Override parameter values based on webapi.xml
     *
     * @param array $inputData Incoming data from request
     * @param array $parameters Contains parameters to replace or default
     * @return array Data in same format as $inputData with appropriate parameters added or changed
     */
    protected function _overrideParams(array $inputData, array $parameters)
    {
        foreach ($parameters as $name => $paramData) {
            if ($paramData[Converter::KEY_FORCE] || !isset($inputData[$name])) {
                $inputData[$name] = $paramData[Converter::KEY_VALUE];
            }
        }
        return $inputData;
    }
}
