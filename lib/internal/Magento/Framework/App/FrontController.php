<?php
/**
 * Front controller responsible for dispatching application requests
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontController implements FrontControllerInterface
{
    /**
     * @var RouterList
     */
    protected $_routerList;

    /**
     * Application state
     *
     * @var State
     */
    protected $appState;

    /**
     * Message manager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param RouterList $routerList
     * @param State $appState
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        RouterList $routerList,
        State $appState,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_routerList = $routerList;
        $this->appState = $appState;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
    }

    /**
     * Perform action and generate response
     *
     * @param RequestInterface $request
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \LogicException
     */
    public function dispatch(RequestInterface $request)
    {
        \Magento\Framework\Profiler::start('routers_match');
        $routingCycleCounter = 0;
        $result = null;
        while (!$request->isDispatched() && $routingCycleCounter++ < 100) {
            $result = $this->processRequest($request);
        }
        \Magento\Framework\Profiler::stop('routers_match');
        if ($routingCycleCounter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $result;
    }

    /**
     * Handle exception
     *
     * @param \Exception $e
     * @param \Magento\Framework\App\ActionInterface $actionInstance
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function handleException($e, $actionInstance)
    {
        $needToMaskDisplayMessage = !($e instanceof \Magento\Framework\Exception\LocalizedException)
            && ($this->appState->getMode() != State::MODE_DEVELOPER);
        $displayMessage = $needToMaskDisplayMessage
            ? (string)new \Magento\Framework\Phrase('An error occurred while processing your request')
            : $e->getMessage();
        $this->messageManager->addError($displayMessage);
        $this->logger->critical($e->getMessage());
        return $actionInstance->getDefaultRedirect();
    }

    /**
     * Route request and dispatch it
     *
     * @param RequestInterface $request
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|null
     */
    protected function processRequest(RequestInterface $request)
    {
        $result = null;
        /** @var \Magento\Framework\App\RouterInterface $router */
        foreach ($this->_routerList as $router) {
            try {
                $actionInstance = $router->match($request);
                if ($actionInstance) {
                    $request->setDispatched(true);
                    $actionInstance->getResponse()->setNoCacheHeaders();
                    try {
                        $result = $actionInstance->dispatch($request);
                    } catch (Action\NotFoundException $e) {
                        throw $e;
                    } catch (\Exception $e) {
                        $result = $this->handleException($e, $actionInstance);
                    }
                    break;
                }
            } catch (Action\NotFoundException $e) {
                $request->initForward();
                $request->setActionName('noroute');
                $request->setDispatched(false);
                break;
            }
        }
        return $result;
    }
}
