<?php
/**
 * Default application router
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionInterface;

class DefaultRouter implements RouterInterface
{
    /**
     * @var NoRouteHandlerList
     */
    protected $noRouteHandlerList;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @param ActionFactory $actionFactory
     * @param NoRouteHandlerList $noRouteHandlerList
     */
    public function __construct(ActionFactory $actionFactory, NoRouteHandlerList $noRouteHandlerList)
    {
        $this->actionFactory = $actionFactory;
        $this->noRouteHandlerList = $noRouteHandlerList;
    }

    /**
     * Modify request and set to no-route action
     *
     * @param RequestInterface $request
     * @return ActionInterface
     */
    public function match(RequestInterface $request)
    {
        foreach ($this->noRouteHandlerList->getHandlers() as $noRouteHandler) {
            if ($noRouteHandler->process($request)) {
                break;
            }
        }

        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }
}
