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
     * @param RouterListInterface $routerList
     * @param ResponseInterface $response
     * @param RequestValidator|null $requestValidator
     * @param MessageManager|null $messageManager
     */
    public function __construct(
        RouterListInterface $routerList,
        ResponseInterface $response,
        ?RequestValidator $requestValidator = null,
        ?MessageManager $messageManager = null
    ) {
        $this->_routerList = $routerList;
        $this->response = $response;
        $this->requestValidator = $requestValidator
            ?? ObjectManager::getInstance()->get(RequestValidator::class);
        $this->messages = $messageManager
            ?? ObjectManager::getInstance()->get(MessageManager::class);
    }

    /**
     * Perform action and generate response
     *
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     * @throws \LogicException
     */
    public function dispatch(RequestInterface $request)
    {
        \Magento\Framework\Profiler::start('routers_match');
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
     * @param RequestInterface $request
     * @param ActionInterface $actionInstance
     * @throws NotFoundException
     *
     * @return ResponseInterface|ResultInterface
     */
    private function processRequest(
        RequestInterface $request,
        ActionInterface $actionInstance
    ) {
        $request->setDispatched(true);
        $this->response->setNoCacheHeaders();
        //Validating request.
        try {
            $this->requestValidator->validate(
                $request,
                $actionInstance
            );

            if ($actionInstance instanceof AbstractAction) {
                $result = $actionInstance->dispatch($request);
            } else {
                $result = $actionInstance->execute();
            }
        } catch (InvalidRequestException $exception) {
            //Validation failed - processing validation results.
            $result = $exception->getReplaceResult();
            if ($messages = $exception->getMessages()) {
                foreach ($messages as $message) {
                    $this->messages->addErrorMessage($message);
                }
            }
        }

        return $result;
    }
}
