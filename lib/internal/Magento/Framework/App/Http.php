<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Registry;

/**
 * HTTP web application. Called from webroot index.php to serve web requests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Http implements \Magento\Framework\AppInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Manager
     */
    protected $_eventManager;

    /**
     * @var AreaList
     */
    protected $_areaList;

    /**
     * @var Request\Http
     */
    protected $_request;

    /**
     * @var ConfigLoaderInterface
     */
    protected $_configLoader;

    /**
     * @var State
     */
    protected $_state;

    /**
     * @var ResponseHttp
     */
    protected $_response;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var ExceptionHandlerInterface
     */
    private $exceptionHandler;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $eventManager
     * @param AreaList $areaList
     * @param RequestHttp $request
     * @param ResponseHttp $response
     * @param ConfigLoaderInterface $configLoader
     * @param State $state
     * @param Registry $registry
     * @param ExceptionHandlerInterface $exceptionHandler
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $eventManager,
        AreaList $areaList,
        RequestHttp $request,
        ResponseHttp $response,
        ConfigLoaderInterface $configLoader,
        State $state,
        Registry $registry,
        ?ExceptionHandlerInterface $exceptionHandler = null
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_areaList = $areaList;
        $this->_request = $request;
        $this->_response = $response;
        $this->_configLoader = $configLoader;
        $this->_state = $state;
        $this->registry = $registry;
        $this->exceptionHandler = $exceptionHandler ?: $this->_objectManager->get(ExceptionHandlerInterface::class);
    }

    /**
     * Run application
     *
     * @return ResponseInterface
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function launch()
    {
        $areaCode = $this->_areaList->getCodeByFrontName($this->_request->getFrontName());
        $this->_state->setAreaCode($areaCode);
        $this->_objectManager->configure($this->_configLoader->load($areaCode));
        /** @var \Magento\Framework\App\FrontControllerInterface $frontController */
        $frontController = $this->_objectManager->get(\Magento\Framework\App\FrontControllerInterface::class);
        $result = $frontController->dispatch($this->_request);
        // TODO: Temporary solution until all controllers return ResultInterface (MAGETWO-28359)
        if ($result instanceof ResultInterface) {
            $this->registry->register('use_page_cache_plugin', true, true);
            $result->renderResult($this->_response);
        } elseif ($result instanceof HttpInterface) {
            $this->_response = $result;
        } else {
            throw new \InvalidArgumentException('Invalid return type');
        }
        if ($this->_request->isHead() && $this->_response->getHttpResponseCode() == 200) {
            $this->handleHeadRequest();
        }
        // This event gives possibility to launch something before sending output (allow cookie setting)
        $eventParams = ['request' => $this->_request, 'response' => $this->_response];
        $this->_eventManager->dispatch('controller_front_send_response_before', $eventParams);
        return $this->_response;
    }

    /**
     * Handle HEAD requests by adding the Content-Length header and removing the body from the response.
     *
     * @return void
     */
    private function handleHeadRequest()
    {
        // It is possible that some PHP installations have overloaded strlen to use mb_strlen instead.
        // This means strlen might return the actual number of characters in a non-ascii string instead
        // of the number of bytes. Use mb_strlen explicitly with a single byte character encoding to ensure
        // that the content length is calculated in bytes.
        $contentLength = mb_strlen($this->_response->getContent(), '8bit');
        $this->_response->clearBody();
        $this->_response->setHeader('Content-Length', $contentLength);
    }

    /**
     * @inheritdoc
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception): bool
    {
        return $this->exceptionHandler->handle($bootstrap, $exception, $this->_response, $this->_request);
    }
}
