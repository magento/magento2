<?php
/**
 * Backend no route handler
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Router;

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
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     */
    public function __construct(
        \Magento\Backend\Helper\Data $helper,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->helper = $helper;
        $this->routeConfig = $routeConfig;
        $this->backendUrl = $backendUrl;
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

        if ($areaFrontName == $this->helper->getAreaFrontName()) {
            $baseUrl = $this->backendUrl->getBaseUrl();
            if (!stripos($baseUrl, $_SERVER['HTTP_HOST']) === false)
            {
                $moduleName = $this->routeConfig->getRouteFrontName('adminhtml');
                $actionNamespace = 'noroute';
                $actionName = 'index';
                $request->setModuleName($moduleName)->setControllerName($actionNamespace)->setActionName($actionName);
                return true;
            }
        }
        return false;
    }
}
