<?php
/**
 * Backend no route handler
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\App\Router;

class NoRouteHandler implements \Magento\App\Router\NoRouteHandlerInterface
{
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\App\Route\ConfigInterface
     */
    protected $_routeConfig;

    /**
     * @param \Magento\Backend\Helper\Data $helper
     * @param \Magento\App\Route\ConfigInterface $routeConfig
     */
    public function __construct(
        \Magento\Backend\Helper\Data $helper,
        \Magento\App\Route\ConfigInterface $routeConfig
    ) {
        $this->_helper = $helper;
        $this->_routeConfig = $routeConfig;
    }

    /**
     * Check and process no route request
     *
     * @param \Magento\App\RequestInterface $request
     * @return bool
     */
    public function process(\Magento\App\RequestInterface $request)
    {
        $requestPathParams = explode('/', trim($request->getPathInfo(), '/'));
        $areaFrontName = array_shift($requestPathParams);

        if ($areaFrontName == $this->_helper->getAreaFrontName()) {

            $moduleName = $this->_routeConfig->getRouteFrontName('adminhtml');
            $controllerName = 'noroute';
            $actionName = 'index';

            $request->setModuleName($moduleName)->setControllerName($controllerName)->setActionName($actionName);

            return true;
        }

        return false;
    }
}
