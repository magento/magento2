<?php
/**
 * Backend no route handler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Router;

/**
 * @api
 */
class NoRouteHandler implements \Magento\Framework\App\Router\NoRouteHandlerInterface
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $routeConfig;

    /**
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     */
    public function __construct(
        \Magento\Backend\Helper\Data $helper,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig
    ) {
        $this->helper = $helper;
        $this->routeConfig = $routeConfig;
    }

    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function process(\Magento\Framework\App\RequestInterface $request)
    {
        $requestPathParams = explode('/', trim($request->getPathInfo(), '/'));
        $areaFrontName = array_shift($requestPathParams);

        if ($areaFrontName === $this->helper->getAreaFrontName(true)) {
            $moduleName = $this->routeConfig->getRouteFrontName('adminhtml');
            $actionNamespace = 'noroute';
            $actionName = 'index';
            $request->setModuleName($moduleName)->setControllerName($actionNamespace)->setActionName($actionName);
            return true;
        }
        return false;
    }
}
