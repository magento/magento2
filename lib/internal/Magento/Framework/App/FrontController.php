<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\Request\ValidatorInterface as RequestValidator;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\Profiler;
use Psr\Log\LoggerInterface;

/**
 * Front controller responsible for dispatching application requests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FrontController implements FrontControllerInterface
{
    /**
     * @var RouterListInterface
     */
    protected $_routerList;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var RequestValidator
     */
    private $requestValidator;

    /**
     * @var MessageManager
     */
    private $messages;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $validatedRequest = false;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var AreaList
     */
    private $areaList;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RouterListInterface $routerList
     * @param ResponseInterface $response
     * @param RequestValidator|null $requestValidator
     * @param MessageManager|null $messageManager
     * @param LoggerInterface|null $logger
     * @param State|null $appState
     * @param AreaList|null $areaList
     * @param ActionFlag|null $actionFlag
     * @param EventManagerInterface|null $eventManager
     * @param RequestInterface|null $request
     */
    public function __construct(
        RouterListInterface $routerList,
        ResponseInterface $response,
        ?RequestValidator $requestValidator = null,
        ?MessageManager $messageManager = null,
        ?LoggerInterface $logger = null,
        ?State $appState = null,
        ?AreaList $areaList = null,
        ?ActionFlag $actionFlag = null,
        ?EventManagerInterface $eventManager = null,
        ?RequestInterface $request = null
    ) {
        $this->_routerList = $routerList;
        $this->response = $response;
        $this->requestValidator = $requestValidator
            ?? ObjectManager::getInstance()->get(RequestValidator::class);
        $this->messages = $messageManager
            ?? ObjectManager::getInstance()->get(MessageManager::class);
        $this->logger = $logger
            ?? ObjectManager::getInstance()->get(LoggerInterface::class);
        $this->appState = $appState
            ?? ObjectManager::getInstance()->get(State::class);
        $this->areaList = $areaList
            ?? ObjectManager::getInstance()->get(AreaList::class);
        $this->actionFlag = $actionFlag
            ?? ObjectManager::getInstance()->get(ActionFlag::class);
        $this->eventManager = $eventManager
            ?? ObjectManager::getInstance()->get(EventManagerInterface::class);
        $this->request = $request
            ?? ObjectManager::getInstance()->get(RequestInterface::class);
    }

    /**
     * Perform action and generate response
     *
     * @param RequestInterface|HttpRequest $request
     * @return ResponseInterface|ResultInterface
     * @throws \LogicException
     * @throws LocalizedException
     */
    public function dispatch(RequestInterface $request)
    {
        Profiler::start('routers_match');
        $this->validatedRequest = false;
        $routingCycleCounter = 0;
        $result = null;
        while (!$request->isDispatched() && $routingCycleCounter++ < 100) {
            /** @var \Magento\Framework\App\RouterInterface $router */
            foreach ($this->_routerList as $router) {
                try {
                    $actionInstance = $router->match($request);
                    if ($actionInstance) {
                        $result = $this->processRequest(
                            $request,
                            $actionInstance
                        );
                        break;
                    }
                } catch (\Magento\Framework\Exception\NotFoundException $e) {
                    $request->initForward();
                    $request->setActionName('noroute');
                    $request->setDispatched(false);
                    break;
                }
            }
        }
        Profiler::stop('routers_match');
        if ($routingCycleCounter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $result;
    }

    /**
     * Process (validate and dispatch) the incoming request
     *
     * @param RequestInterface $request
     * @param ActionInterface $actionInstance
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     *
     * @throws NotFoundException
     */
    private function processRequest(
        RequestInterface $request,
        ActionInterface $actionInstance
    ) {
        $request->setDispatched(true);
        $this->response->setNoCacheHeaders();
        $result = null;

        //Validating a request only once.
        if (!$this->validatedRequest) {
            $area = $this->areaList->getArea($this->appState->getAreaCode());
            $area->load(Area::PART_DESIGN);
            $area->load(Area::PART_TRANSLATE);

            try {
                $this->requestValidator->validate($request, $actionInstance);
            } catch (InvalidRequestException $exception) {
                //Validation failed - processing validation results.
                $this->logger->debug(
                    sprintf('Request validation failed for action "%s"', get_class($actionInstance)),
                    ["exception" => $exception]
                );
                $result = $exception->getReplaceResult();
                if ($messages = $exception->getMessages()) {
                    foreach ($messages as $message) {
                        $this->messages->addErrorMessage($message);
                    }
                }
            }
            $this->validatedRequest = true;
        }

        // Validation did not produce a result to replace the action's.
        if (!$result) {
            $this->dispatchPreDispatchEvents($actionInstance, $request);
            $result = $this->getActionResponse($actionInstance, $request);
            if (!$this->isSetActionNoPostDispatchFlag()) {
                $this->dispatchPostDispatchEvents($actionInstance, $request);
            }
        }

        //handling redirect to 404
        if ($result instanceof NotFoundException) {
            throw $result;
        }
        return $result;
    }

    /**
     * Return the result of processed request
     *
     * There are 3 ways of handling requests:
     * - Result without dispatching event when FLAG_NO_DISPATCH is set, just return ResponseInterface
     * - Backwards-compatible way using `AbstractAction::dispatch` which is deprecated
     * - Correct way for handling requests with `ActionInterface::execute`
     *
     * @param ActionInterface $actionInstance
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     * @throws NotFoundException
     */
    private function getActionResponse(ActionInterface $actionInstance, RequestInterface $request)
    {
        if ($this->actionFlag->get('', ActionInterface::FLAG_NO_DISPATCH)) {
            return $this->response;
        }

        if ($actionInstance instanceof AbstractAction) {
            return $actionInstance->dispatch($request);
        }

        return $actionInstance->execute();
    }

    /**
     * Check if action flags are set that would suppress the post dispatch events.
     *
     * @return bool
     */
    private function isSetActionNoPostDispatchFlag(): bool
    {
        return $this->actionFlag->get('', ActionInterface::FLAG_NO_DISPATCH)
            || $this->actionFlag->get('', ActionInterface::FLAG_NO_POST_DISPATCH);
    }

    /**
     * Dispatch the controller_action_predispatch events.
     *
     * @param ActionInterface $actionInstance
     * @param RequestInterface $request
     * @return void
     */
    private function dispatchPreDispatchEvents(ActionInterface $actionInstance, RequestInterface $request): void
    {
        $this->eventManager->dispatch('controller_action_predispatch', $this->getEventParameters($actionInstance));
        if ($this->request instanceof HttpRequest) {
            $this->eventManager->dispatch(
                'controller_action_predispatch_' . $request->getRouteName(),
                $this->getEventParameters($actionInstance)
            );
            $this->eventManager->dispatch(
                'controller_action_predispatch_' . $request->getFullActionName(),
                $this->getEventParameters($actionInstance)
            );
        }
    }

    /**
     * Dispatch the controller_action_postdispatch events.
     *
     * @param ActionInterface $actionInstance
     * @param RequestInterface $request
     * @return void
     */
    private function dispatchPostDispatchEvents(ActionInterface $actionInstance, RequestInterface $request): void
    {
        Profiler::start('postdispatch');
        if ($this->request instanceof HttpRequest) {
            $this->eventManager->dispatch(
                'controller_action_postdispatch_' . $request->getFullActionName(),
                $this->getEventParameters($actionInstance)
            );
            $this->eventManager->dispatch(
                'controller_action_postdispatch_' . $request->getRouteName(),
                $this->getEventParameters($actionInstance)
            );
        }
        $this->eventManager->dispatch('controller_action_postdispatch', $this->getEventParameters($actionInstance));
        Profiler::stop('postdispatch');
    }

    /**
     * Build the event parameter array
     *
     * @param ActionInterface $subject
     * @return array
     */
    private function getEventParameters(ActionInterface $subject): array
    {
        return ['controller_action' => $subject, 'request' => $this->request];
    }
}
