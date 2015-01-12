<?php
/**
 * Backend router
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Backend\App;

class Router extends \Magento\Core\App\Router\Base
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * @var \Magento\Framework\UrlInterface $url
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
    protected $_requiredParams = ['areaFrontName', 'moduleFrontName', 'actionPath', 'actionName'];

    /**
     * We need to have noroute action in this router
     * not to pass dispatching to next routers
     *
     * @var bool
     */
    protected $applyNoRoute = true;

    /**
     * @var string
     */
    protected $pathPrefix = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;

    /**
     * @param \Magento\Framework\App\Router\ActionList $actionList
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Router\ActionList $actionList,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
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
            $actionList,
            $actionFactory,
            $defaultPath,
            $responseFactory,
            $routeConfig,
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
}
