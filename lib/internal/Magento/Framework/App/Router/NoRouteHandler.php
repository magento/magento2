<?php
/**
 * Default no route handler
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class NoRouteHandler implements NoRouteHandlerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->_config = $config;
    }

    /**
     * Check and process no route request
     *
     * @param RequestInterface $request
     * @return bool
     */
    public function process(RequestInterface $request)
    {
        $noRoutePath = $this->_config->getValue('web/default/no_route', 'default');

        if ($noRoutePath) {
            $noRoute = explode('/', $noRoutePath);
        } else {
            $noRoute = [];
        }

        $moduleName = $noRoute[0] ?? 'cms';
        $actionPath = $noRoute[1] ?? 'index';
        $actionName = $noRoute[2] ?? 'index';

        $request->setModuleName($moduleName)->setControllerName($actionPath)->setActionName($actionName);

        return true;
    }
}
