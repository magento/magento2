<?php
/**
 * Front controller responsible for dispatching application requests
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class FrontController implements FrontControllerInterface
{
    /**
     * @var RouterList
     */
    protected $_routerList;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @param RouterList $routerList
     * @param \Magento\Framework\App\Response\Http $response
     */
    public function __construct(
        RouterList $routerList,
        \Magento\Framework\App\Response\Http $response
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
