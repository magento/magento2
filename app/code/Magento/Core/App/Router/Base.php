<?php
/**
 * Base router
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
namespace Magento\Core\App\Router;

class Base extends \Magento\App\Router\AbstractRouter
{
    /**
     * @var array
     */
    protected $_modules = array();

    /**
     * @var array
     */
    protected $_dispatchData = array();

    /**
     * List of required request parameters
     * Order sensitive
     * @var string[]
     */
    protected $_requiredParams = array(
        'moduleFrontName',
        'controllerName',
        'actionName',
    );

    /**
     * @var \Magento\App\Route\ConfigInterface
     */
    protected $_routeConfig;

    /**
     * Url security information.
     *
     * @var \Magento\Url\SecurityInfoInterface
     */
    protected $_urlSecurityInfo;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * @var \Magento\Code\NameBuilder
     */
    protected $nameBuilder;

    /**
     * @param \Magento\App\ActionFactory $actionFactory
     * @param \Magento\App\DefaultPathInterface $defaultPath
     * @param \Magento\App\ResponseFactory $responseFactory
     * @param \Magento\App\Route\ConfigInterface $routeConfig
     * @param \Magento\App\State $appState
     * @param \Magento\UrlInterface $url
     * @param \Magento\Core\Model\StoreManagerInterface|\Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\Code\NameBuilder $nameBuilder
     * @throws \InvalidArgumentException
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
        \Magento\Code\NameBuilder $nameBuilder
    ) {
        parent::__construct($actionFactory);

        $this->_responseFactory = $responseFactory;
        $this->_defaultPath     = $defaultPath;
        $this->_routeConfig     = $routeConfig;
        $this->_urlSecurityInfo = $urlSecurityInfo;
        $this->_storeConfig     = $storeConfig;
        $this->_url             = $url;
        $this->_storeManager    = $storeManager;
        $this->_appState        = $appState;
        $this->nameBuilder = $nameBuilder;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\App\Action\Action|null
     */
    public function match(\Magento\App\RequestInterface $request)
    {
        $params = $this->_parseRequest($request);

        return $this->_matchController($request, $params);
    }

    /**
     * Parse request URL params
     *
     * @param \Magento\App\RequestInterface $request
     * @return array
     */
    protected function _parseRequest(\Magento\App\RequestInterface $request)
    {
        $output = array();

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', ($path ? $path : $this->_getDefaultPath()));
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i+1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
    }

    /**
     * Match module front name
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $param
     * @return string|null
     */
    protected function _matchModuleFrontName(\Magento\App\RequestInterface $request, $param)
    {
        // get module name
        if ($request->getModuleName()) {
            $moduleFrontName = $request->getModuleName();
        } elseif (!empty($param)) {
            $moduleFrontName = $param;
        } else {
            $moduleFrontName = $this->_defaultPath->getPart('module');
            $request->setAlias(\Magento\Url::REWRITE_REQUEST_PATH_ALIAS, '');
        }
        if (!$moduleFrontName) {
            return null;
        }
        return $moduleFrontName;
    }

    /**
     * Match controller name
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $param
     * @return string
     */
    protected function _matchControllerName(\Magento\App\RequestInterface $request,  $param)
    {
        if ($request->getControllerName()) {
            $controller = $request->getControllerName();
        } elseif (!empty($param)) {
            $controller = $param;
        } else {
            $controller = $this->_defaultPath->getPart('controller');
            $request->setAlias(
                \Magento\Url::REWRITE_REQUEST_PATH_ALIAS,
                ltrim($request->getOriginalPathInfo(), '/')
            );
        }
        return $controller;
    }

    /**
     * Match controller name
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $param
     * @return string
     */
    protected function _matchActionName(\Magento\App\RequestInterface $request, $param)
    {
        if ($request->getActionName()) {
            $action = $request->getActionName();
        } elseif (empty($param)) {
            $action = $this->_defaultPath->getPart('action');
        } else {
            $action = $param;
        }

        return $action;
    }

    /**
     * Get not found controller instance
     *
     * @param string $currentModuleName
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\App\Action\Action|null
     */
    protected function _getNotFoundControllerInstance($currentModuleName, \Magento\App\RequestInterface $request)
    {
        if (!$this->_noRouteShouldBeApplied()) {
            return null;
        }

        $controllerClassName = $this->getControllerClassName($currentModuleName, 'index');
        if (!$controllerClassName || !method_exists($controllerClassName, 'norouteAction')) {
            return null;
        }

        // instantiate controller class
        return $this->_actionFactory->createController($controllerClassName,
            array('request' => $request)
        );
    }

    /**
     * Create matched controller instance
     *
     * @param \Magento\App\RequestInterface $request
     * @param array $params
     * @return \Magento\App\Action\Action|null
     */
    protected function _matchController(\Magento\App\RequestInterface $request, array $params)
    {
        $moduleFrontName = $this->_matchModuleFrontName($request, $params['moduleFrontName']);
        if (empty($moduleFrontName)) {
            return null;
        }

        /**
         * Searching router args by module name from route using it as key
         */
        $modules = $this->_routeConfig->getModulesByFrontName($moduleFrontName);

        if (empty($modules) === true) {
            return null;
        }

        /**
         * Going through modules to find appropriate controller
         */
        $currentModuleName = null;
        $controller = null;
        $action = null;
        $controllerInstance = null;

        $request->setRouteName($this->_routeConfig->getRouteByFrontName($moduleFrontName));
        $controller = $this->_matchControllerName($request, $params['controllerName']);
        $action = $this->_matchActionName($request, $params['actionName']);
        $this->_checkShouldBeSecure($request, '/' . $moduleFrontName . '/' . $controller . '/' . $action);

        foreach ($modules as $moduleName) {
            $currentModuleName = $moduleName;

            $controllerClassName = $this->getControllerClassName($moduleName, $controller);
            if (!$controllerClassName || false === method_exists($controllerClassName, $action . 'Action')) {
                continue;
            }

            $controllerInstance = $this->_actionFactory->createController($controllerClassName,
                array('request' => $request)
            );
            break;
        }

        if (null == $controllerInstance) {
            $controllerInstance = $this->_getNotFoundControllerInstance($currentModuleName, $request);
            if (is_null($controllerInstance)) {
                return null;
            }
            $action = 'noroute';
        }

        // set values only after all the checks are done
        $request->setModuleName($moduleFrontName);
        $request->setControllerName($controller);
        $request->setActionName($action);
        $request->setControllerModule($currentModuleName);
        if (isset($params['variables'])) {
            $request->setParams($params['variables']);
        }
        return $controllerInstance;
    }

    /**
     * Get router default request path
     *
     * @return string
     */
    protected function _getDefaultPath()
    {
        return $this->_storeConfig->getConfig('web/default/front');
    }

    /**
     * Allow to control if we need to enable no route functionality in current router
     *
     * @return bool
     */
    protected function _noRouteShouldBeApplied()
    {
        return false;
    }

    /**
     * Build controller class name
     *
     * @param string $module
     * @param string $controller
     * @return string
     */
    public function getControllerClassName($module, $controller)
    {
        return $this->nameBuilder->buildClassName(array(
            $module,
            'Controller',
            $controller
        ));
    }

    /**
     * Check that request uses https protocol if it should.
     * Function redirects user to correct URL if needed.
     *
     * @param \Magento\App\RequestInterface $request
     * @param string $path
     * @return void
     */
    protected function _checkShouldBeSecure(\Magento\App\RequestInterface $request, $path = '')
    {
        if (!$this->_appState->isInstalled() || $request->getPost()) {
            return;
        }

        if ($this->_shouldBeSecure($path) && !$request->isSecure()) {
            $url = $this->_getCurrentSecureUrl($request);
            if ($this->_shouldRedirectToSecure()) {
                $url = $this->_url->getRedirectUrl($url);
            }

            $this->_responseFactory->create()
                ->setRedirect($url)
                ->sendResponse();
            exit;
        }
    }

    /**
     * Check whether redirect url should be used for secure routes
     *
     * @return bool
     */
    protected function _shouldRedirectToSecure()
    {
        return $this->_url->getUseSession();
    }

    /**
     * Retrieve secure url for current request
     *
     * @param \Magento\App\RequestInterface $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        $alias = $request->getAlias(\Magento\Url::REWRITE_REQUEST_PATH_ALIAS);
        if ($alias) {
            return $this->_storeManager->getStore()->getBaseUrl('link', true) . ltrim($alias, '/');
        }

        return $this->_storeManager->getStore()->getBaseUrl('link', true) . ltrim($request->getPathInfo(), '/');
    }

    /**
     * Check whether given path should be secure according to configuration security requirements for URL
     * "Secure" should not be confused with https protocol, it is about web/secure/*_url settings usage only
     *
     * @param string $path
     * @return bool
     */
    protected function _shouldBeSecure($path)
    {
        return parse_url($this->_storeConfig->getConfig('web/unsecure/base_url'), PHP_URL_SCHEME) === 'https'
            || $this->_storeConfig->getConfigFlag(\Magento\Core\Model\Store::XML_PATH_SECURE_IN_FRONTEND)
                && parse_url($this->_storeConfig->getConfig('web/secure/base_url'), PHP_URL_SCHEME) == 'https'
                && $this->_urlSecurityInfo->isSecure($path);
    }
}
