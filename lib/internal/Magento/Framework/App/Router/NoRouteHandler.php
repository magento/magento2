<?php
/**
 * Default no route handler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

class NoRouteHandler implements \Magento\Framework\App\Router\NoRouteHandlerInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->_config = $config;
    }

    /**
     * Check and process no route request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process(\Magento\Framework\App\RequestInterface $request)
    {
        $noRoutePath = $this->_config->getValue('web/default/no_route', 'default');

        if ($noRoutePath) {
            $noRoute = explode('/', $noRoutePath, 4);
        } else {
            $noRoute = [];
        }

        $moduleName = isset($noRoute[0]) ? $noRoute[0] : 'core';
        $actionPath = isset($noRoute[1]) ? $noRoute[1] : 'index';
        $actionName = isset($noRoute[2]) ? $noRoute[2] : 'index';
        $params = isset($noRoute[3]) ? explode('/', $noRoute[3]) : [];

        $actionParams = [];
        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $actionParams[$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
        }

        $request->setModuleName($moduleName);
        $request->setControllerName($actionPath);
        $request->setActionName($actionName);
        $request->setParams($actionParams);

        return true;
    }
}
