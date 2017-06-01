<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\Router\ActionList;

/**
 * Matches applicaton action in case when robots.txt file was requested
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var ActionList
     */
    private $actionList;

    /**
     * @var ConfigInterface
     */
    private $routeConfig;

    /**
     * @param ActionFactory $actionFactory
     * @param ActionList $actionList
     * @param ConfigInterface $routeConfig
     */
    public function __construct(
        ActionFactory $actionFactory,
        ActionList $actionList,
        ConfigInterface $routeConfig
    )
    {
        $this->actionFactory = $actionFactory;
        $this->actionList = $actionList;
        $this->routeConfig = $routeConfig;
    }

    /**
     * Checks if robots.txt file was requested and returns instance of matched application action class
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        if ($identifier !== 'robots.txt') {
            return null;
        }

        $modules = $this->routeConfig->getModulesByFrontName('robots');
        if (empty($modules)) {
            return null;
        }

        $request->setModuleName('robots');
        $request->setControllerName('index');
        $request->setActionName('index');

        $actionClassName = $this->actionList->get($modules[0], null, 'index', 'index');
        $actionInstance = $this->actionFactory->create($actionClassName);
        return $actionInstance;
    }
}
