<?php
/**
 * Front controller responsible for dispatching application requests
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Class \Magento\Framework\App\FrontController
 *
 */
class FrontController implements FrontControllerInterface
{
    /**
     * @var RouterListInterface
     */
    protected $_routerList;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @param RouterListInterface $routerList
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        RouterListInterface $routerList,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->_routerList = $routerList;
        $this->response = $response;
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
            /** @var \Magento\Framework\App\RouterInterface $router */
            foreach ($this->_routerList as $router) {
                try {
                    $actionInstance = $router->match($request);
                    if ($actionInstance) {
                        $request->setDispatched(true);
                        $this->response->setNoCacheHeaders();
                        if ($actionInstance instanceof \Magento\Framework\App\Action\AbstractAction) {
                            $result = $actionInstance->dispatch($request);
                        } else {
                            $result = $actionInstance->execute();
                        }
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
}
