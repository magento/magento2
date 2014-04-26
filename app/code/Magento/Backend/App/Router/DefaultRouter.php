<?php
/**
 * Backend router
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
 *
 */
namespace Magento\Backend\App\Router;

class DefaultRouter extends \Magento\Core\App\Router\Base
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var \Magento\Core\Model\Url|\Magento\Framework\UrlInterface $url
     */
    protected $_url;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * List of required request parameters
     * Order sensitive
     *
     * @var string[]
     */
    protected $_requiredParams = array('areaFrontName', 'moduleFrontName', 'controllerName', 'actionName');

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo,
        $routerId,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        parent::__construct(
            $actionFactory,
            $defaultPath,
            $responseFactory,
            $routeConfig,
            $appState,
            $url,
            $storeManager,
            $scopeConfig,
            $urlSecurityInfo,
            $routerId,
            $nameBuilder
        );
        $this->_coreConfig = $coreConfig;
        $this->_backendConfig = $backendConfig;
        $this->_url = $url;
    }

    /**
     * Get router default request path
     * @return string
     */
    protected function _getDefaultPath()
    {
        return (string)$this->_backendConfig->getValue('web/default/admin');
    }

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return true;
    }

    /**
     * Check whether URL for corresponding path should use https protocol
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _shouldBeSecure($path)
    {
        return substr(
            (string)$this->_coreConfig->getValue('web/unsecure/base_url', 'default'),
            0,
            5
        ) === 'https' || $this->_backendConfig->isSetFlag(
            'web/secure/use_in_adminhtml'
        ) && substr(
            (string)$this->_coreConfig->getValue('web/secure/base_url', 'default'),
            0,
            5
        ) === 'https';
    }

    /**
     * Retrieve current secure url
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        return $this->_url->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * Check whether redirect should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return false;
    }

    /**
     * Build controller class name based on moduleName and controllerName
     *
     * @param string $module
     * @param string $controller
     * @return string
     */
    public function getControllerClassName($module, $controller)
    {
        $parts = explode('_', $module);
        $parts = array_splice($parts, 0, 2);
        $parts[] = 'Controller';
        $parts[] = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
        $parts[] = $controller;

        return $this->nameBuilder->buildClassName($parts);
    }
}
