<?php
/**
 * Front controller responsible for dispatching application requests
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Request\ValidatorInterface as RequestValidator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\App\Action\AbstractAction;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
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
     * @param RouterListInterface $routerList
     * @param ResponseInterface $response
     * @param RequestValidator|null $requestValidator
     * @param MessageManager|null $messageManager
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        RouterListInterface $routerList,
        ResponseInterface $response,
        ?RequestValidator $requestValidator = null,
        ?MessageManager $messageManager = null,
        ?LoggerInterface $logger = null
    ) {
        $this->_routerList = $routerList;
        $this->response = $response;
        $this->requestValidator = $requestValidator
            ?? ObjectManager::getInstance()->get(RequestValidator::class);
        $this->messages = $messageManager
            ?? ObjectManager::getInstance()->get(MessageManager::class);
        $this->logger = $logger
            ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Perform action and generate response
     *
     * @param RequestInterface|HttpRequest $request
     * @return ResponseInterface|ResultInterface
     * @throws \LogicException
     */
    public function dispatch(RequestInterface $request)
    {
        \Magento\Framework\Profiler::start('routers_match');
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
        \Magento\Framework\Profiler::stop('routers_match');
        if ($routingCycleCounter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $result;
    }

    /**
     * @param HttpRequest $request
     * @param ActionInterface $actionInstance
     * @throws NotFoundException
     *
     * @return ResponseInterface|ResultInterface
     */
    private function processRequest(
        HttpRequest $request,
        ActionInterface $actionInstance
    ) {
        $request->setDispatched(true);
        $this->response->setNoCacheHeaders();
        $result = null;

        //Validating a request only once.
        if (!$this->validatedRequest) {
            try {
                $this->requestValidator->validate(
                    $request,
                    $actionInstance
                );
            } catch (InvalidRequestException $exception) {
                //Validation failed - processing validation results.
                $this->logger->debug(
                    'Request validation failed for action "'
                    .get_class($actionInstance) .'"'
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

        //Validation did not produce a result to replace the action's.
        if (!$result) {
            if ($actionInstance instanceof AbstractAction) {
                $result = $actionInstance->dispatch($request);
            } else {
                $result = $actionInstance->execute();
            }
        }

        //handling redirect to 404
        if ($result instanceof NotFoundException) {
            throw $result;
        }
        return $result;
    }
}
