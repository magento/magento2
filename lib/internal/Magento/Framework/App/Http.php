<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event;
use Magento\Framework\Filesystem;

/**
 * HTTP web application. Called from webroot index.php to serve web requests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Http implements \Magento\Framework\AppInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Event\Manager
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
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var ResponseHttp
     */
    protected $_response;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Event\Manager $eventManager
     * @param AreaList $areaList
     * @param RequestHttp $request
     * @param ResponseHttp $response
     * @param ConfigLoaderInterface $configLoader
     * @param State $state
     * @param Filesystem $filesystem,
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Event\Manager $eventManager,
        AreaList $areaList,
        RequestHttp $request,
        ResponseHttp $response,
        ConfigLoaderInterface $configLoader,
        State $state,
        Filesystem $filesystem,
        \Magento\Framework\Registry $registry
    ) {
        $this->_objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_areaList = $areaList;
        $this->_request = $request;
        $this->_response = $response;
        $this->_configLoader = $configLoader;
        $this->_state = $state;
        $this->_filesystem = $filesystem;
        $this->registry = $registry;
    }

    /**
     * Add new dependency
     *
     * @return \Psr\Log\LoggerInterface
     *
     * @deprecated
     */
    private function getLogger()
    {
        if (!$this->logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        }
        return $this->logger;
    }

    /**
     * Run application
     *
     * @throws \InvalidArgumentException
     * @return ResponseInterface
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
        // This event gives possibility to launch something before sending output (allow cookie setting)
        $eventParams = ['request' => $this->_request, 'response' => $this->_response];
        $this->_eventManager->dispatch('controller_front_send_response_before', $eventParams);
        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        $result = $this->handleDeveloperMode($bootstrap, $exception)
            || $this->handleBootstrapErrors($bootstrap, $exception)
            || $this->handleSessionException($exception)
            || $this->handleInitException($exception)
            || $this->handleGenericReport($bootstrap, $exception);
        return $result;
    }

    /**
     * Error handler for developer mode
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    private function handleDeveloperMode(Bootstrap $bootstrap, \Exception $exception)
    {
        if ($bootstrap->isDeveloperMode()) {
            if (Bootstrap::ERR_IS_INSTALLED == $bootstrap->getErrorCode()) {
                try {
                    $this->redirectToSetup($bootstrap, $exception);
                    return true;
                } catch (\Exception $e) {
                    $exception = $e;
                }
            }
            $this->_response->setHttpResponseCode(500);
            $this->_response->setHeader('Content-Type', 'text/plain');
            $this->_response->setBody($this->buildContentFromException($exception));
            $this->_response->sendResponse();
            return true;
        }
        return false;
    }

    /**
     * Build content based on an exception
     *
     * @param \Exception $exception
     * @return string
     */
    private function buildContentFromException(\Exception $exception)
    {
        /** @var \Exception[] $exceptions */
        $exceptions = [];
        do {
            $exceptions[] = $exception;
        } while ($exception = $exception->getPrevious());

        $buffer = sprintf("%d exception(s):\n", count($exceptions));

        foreach ($exceptions as $index => $exception) {
            $buffer .= sprintf("Exception #%d (%s): %s\n", $index, get_class($exception), $exception->getMessage());
        }

        foreach ($exceptions as $index => $exception) {
            $buffer .= sprintf(
                "\nException #%d (%s): %s\n%s\n",
                $index,
                get_class($exception),
                $exception->getMessage(),
                $exception->getTraceAsString()
            );
        }

        return $buffer;
    }

    /**
     * If not installed, try to redirect to installation wizard
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return void
     * @throws \Exception
     */
    private function redirectToSetup(Bootstrap $bootstrap, \Exception $exception)
    {
        $setupInfo = new SetupInfo($bootstrap->getParams());
        $projectRoot = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        if ($setupInfo->isAvailable()) {
            $this->_response->setRedirect($setupInfo->getUrl());
            $this->_response->sendHeaders();
        } else {
            $newMessage = $exception->getMessage() . "\nNOTE: You cannot install Magento using the Setup Wizard "
                . "because the Magento setup directory cannot be accessed. \n"
                . 'You can install Magento using either the command line or you must restore access '
                . 'to the following directory: ' . $setupInfo->getDir($projectRoot) . "\n";

            throw new \Exception($newMessage, 0, $exception);
        }
    }

    /**
     * Handler for bootstrap errors
     *
     * @param Bootstrap $bootstrap
     * @param \Exception &$exception
     * @return bool
     */
    private function handleBootstrapErrors(Bootstrap $bootstrap, \Exception &$exception)
    {
        $bootstrapCode = $bootstrap->getErrorCode();
        if (Bootstrap::ERR_MAINTENANCE == $bootstrapCode) {
            require $this->_filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('errors/503.php');
            return true;
        }
        if (Bootstrap::ERR_IS_INSTALLED == $bootstrapCode) {
            try {
                $this->redirectToSetup($bootstrap, $exception);
                return true;
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        return false;
    }

    /**
     * Handler for session errors
     *
     * @param \Exception $exception
     * @return bool
     */
    private function handleSessionException(\Exception $exception)
    {
        if ($exception instanceof \Magento\Framework\Exception\SessionException) {
            $this->_response->setRedirect($this->_request->getDistroBaseUrl());
            $this->_response->sendHeaders();
            return true;
        }
        return false;
    }

    /**
     * Handler for application initialization errors
     *
     * @param \Exception $exception
     * @return bool
     */
    private function handleInitException(\Exception $exception)
    {
        if ($exception instanceof \Magento\Framework\Exception\State\InitException) {
            $this->getLogger()->critical($exception);
            require $this->_filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('errors/404.php');
            return true;
        }
        return false;
    }

    /**
     * Handle for any other errors
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    private function handleGenericReport(Bootstrap $bootstrap, \Exception $exception)
    {
        $reportData = [$exception->getMessage(), $exception->getTraceAsString()];
        $params = $bootstrap->getParams();
        if (isset($params['REQUEST_URI'])) {
            $reportData['url'] = $params['REQUEST_URI'];
        }
        if (isset($params['SCRIPT_NAME'])) {
            $reportData['script_name'] = $params['SCRIPT_NAME'];
        }
        require $this->_filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath('errors/report.php');
        return true;
    }
}
