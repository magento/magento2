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
     * @var \Magento\Core\Model\Url|\Magento\UrlInterface $url
     */
    protected $_url;

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $_coreConfig;

    /**
     * List of required request parameters
     * Order sensitive
     *
     * @var string[]
     */
    protected $_requiredParams = array(
        'areaFrontName',
        'moduleFrontName',
        'controllerName',
        'actionName',
    );

    /**
     * @param \Magento\App\ActionFactory $actionFactory
     * @param \Magento\App\DefaultPathInterface $defaultPath
     * @param \Magento\App\ResponseFactory $responseFactory
     * @param \Magento\App\Route\ConfigInterface $routeConfig
     * @param \Magento\App\State $appState
     * @param \Magento\UrlInterface $url
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\App\ConfigInterface $coreConfig
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\Code\NameBuilder $nameBuilder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\App\ActionFactory $actionFactory,
        \Magento\App\DefaultPathInterface $defaultPath,
        \Magento\App\ResponseFactory $responseFactory,
        \Magento\App\Route\ConfigInterface $routeConfig,
        \Magento\App\State $appState,
        \Magento\UrlInterface $url,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Url\SecurityInfoInterface $urlSecurityInfo,
        $routerId,
        \Magento\Code\NameBuilder $nameBuilder,
        \Magento\App\ConfigInterface $coreConfig,
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
            $storeConfig,
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
        return substr((string)$this->_coreConfig->getValue('web/unsecure/base_url', 'default'), 0, 5) === 'https'
            || $this->_backendConfig->isSetFlag('web/secure/use_in_adminhtml')
            && substr((string)$this->_coreConfig->getValue('web/secure/base_url', 'default'), 0, 5) === 'https';
    }

    /**
     * Retrieve current secure url
     *
     * @param \Magento\App\RequestInterface $request
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
