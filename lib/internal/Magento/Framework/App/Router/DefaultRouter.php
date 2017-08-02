<?php
/**
 * Default application router
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionInterface;

/**
 * Class \Magento\Framework\App\Router\DefaultRouter
 *
 * @since 2.0.0
 */
class DefaultRouter implements RouterInterface
{
    /**
     * @var NoRouteHandlerList
     * @since 2.0.0
     */
    protected $noRouteHandlerList;

    /**
     * @var ActionFactory
     * @since 2.0.0
     */
    protected $actionFactory;

    /**
     * @param ActionFactory $actionFactory
     * @param NoRouteHandlerList $noRouteHandlerList
     * @since 2.0.0
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
     * @since 2.0.0
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
