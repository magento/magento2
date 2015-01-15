<?php
/**
 * Base router
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\App\Router;

use Magento\Framework\App\RequestInterface;

class Base implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * @var string
     */
    protected $actionInterface = '\Magento\Framework\App\ActionInterface';

    /**
     * @var array
     */
    protected $_modules = [];

    /**
     * @var array
     */
    protected $_dispatchData = [];

    /**
     * List of required request parameters
     * Order sensitive
     * @var string[]
     */
    protected $_requiredParams = ['moduleFrontName', 'actionPath', 'actionName'];

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     */
    protected $_routeConfig;

    /**
     * Url security information.
     *
     * @var \Magento\Framework\Url\SecurityInfoInterface
     */
    protected $_urlSecurityInfo;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $nameBuilder;

    /**
     * @var array
     */
    protected $reservedNames = ['new', 'print', 'switch', 'return'];

    /**
     * Allows to control if we need to enable no route functionality in current router
     *
     * @var bool
     */
    protected $applyNoRoute = false;

    /**
     * @var string
     */
    protected $pathPrefix = null;

    /**
     * @var \Magento\Framework\App\Router\ActionList
     */
    protected $actionList;

    /**
     * @param \Magento\Framework\App\Router\ActionList $actionList
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Store\Model\StoreManagerInterface|\Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @throws \InvalidArgumentException
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
        \Magento\Framework\Code\NameBuilder $nameBuilder
    ) {
        $this->actionList = $actionList;
        $this->actionFactory = $actionFactory;
        $this->_responseFactory = $responseFactory;
        $this->_defaultPath = $defaultPath;
        $this->_routeConfig = $routeConfig;
        $this->_urlSecurityInfo = $urlSecurityInfo;
        $this->_scopeConfig = $scopeConfig;
        $this->_url = $url;
        $this->_storeManager = $storeManager;
        $this->nameBuilder = $nameBuilder;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\Action\Action|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $params = $this->parseRequest($request);

        return $this->matchAction($request, $params);
    }

    /**
     * Parse request URL params
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return array
     */
    protected function parseRequest(\Magento\Framework\App\RequestInterface $request)
    {
        $output = [];

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', $path ? $path : $this->_getDefaultPath());
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
    }

    /**
     * Match module front name
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $param
     * @return string|null
     */
    protected function matchModuleFrontName(\Magento\Framework\App\RequestInterface $request, $param)
    {
        // get module name
        if ($request->getModuleName()) {
            $moduleFrontName = $request->getModuleName();
        } elseif (!empty($param)) {
            $moduleFrontName = $param;
        } else {
            $moduleFrontName = $this->_defaultPath->getPart('module');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, '');
        }
        if (!$moduleFrontName) {
            return null;
        }
        return $moduleFrontName;
    }

    /**
     * Match controller name
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $param
     * @return string
     */
    protected function matchActionPath(\Magento\Framework\App\RequestInterface $request, $param)
    {
        if ($request->getControllerName()) {
            $actionPath = $request->getControllerName();
        } elseif (!empty($param)) {
            $actionPath = $param;
        } else {
            $actionPath = $this->_defaultPath->getPart('controller');
            $request->setAlias(
                \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                ltrim($request->getOriginalPathInfo(), '/')
            );
        }
        return $actionPath;
    }

    /**
     * Get not found controller instance
     *
     * @param string $currentModuleName
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\Action\Action|null
     */
    protected function getNotFoundAction($currentModuleName, RequestInterface $request)
    {
        if (!$this->applyNoRoute) {
            return null;
        }

        $actionClassName = $this->getActionClassName($currentModuleName, 'noroute');
        if (!$actionClassName || !is_subclass_of($actionClassName, $this->actionInterface)) {
            return null;
        }

        // instantiate action class
        return $this->actionFactory->create($actionClassName, ['request' => $request]);
    }

    /**
     * Create matched controller instance
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $params
     * @return \Magento\Framework\App\Action\Action|null
     */
    protected function matchAction(\Magento\Framework\App\RequestInterface $request, array $params)
    {
        $moduleFrontName = $this->matchModuleFrontName($request, $params['moduleFrontName']);
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
        $actionPath = null;
        $action = null;
        $actionInstance = null;

        $actionPath = $this->matchActionPath($request, $params['actionPath']);
        $action = $request->getActionName() ?: ($params['actionName'] ?: $this->_defaultPath->getPart('action'));
        $this->_checkShouldBeSecure($request, '/' . $moduleFrontName . '/' . $actionPath . '/' . $action);

        foreach ($modules as $moduleName) {
            $currentModuleName = $moduleName;

            $actionClassName = $this->actionList->get($moduleName, $this->pathPrefix, $actionPath, $action);
            if (!$actionClassName || !is_subclass_of($actionClassName, $this->actionInterface)) {
                continue;
            }

            $actionInstance = $this->actionFactory->create($actionClassName, ['request' => $request]);
            break;
        }

        if (null == $actionInstance) {
            $actionInstance = $this->getNotFoundAction($currentModuleName, $request);
            if (is_null($actionInstance)) {
                return null;
            }
            $action = 'noroute';
        }

        // set values only after all the checks are done
        $request->setModuleName($moduleFrontName);
        $request->setControllerName($actionPath);
        $request->setActionName($action);
        $request->setControllerModule($currentModuleName);
        $request->setRouteName($this->_routeConfig->getRouteByFrontName($moduleFrontName));
        if (isset($params['variables'])) {
            $request->setParams($params['variables']);
        }
        return $actionInstance;
    }

    /**
     * Get router default request path
     *
     * @return string
     */
    protected function _getDefaultPath()
    {
        return $this->_scopeConfig->getValue('web/default/front', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Build controller class name
     *
     * @param string $module
     * @param string $actionPath
     * @return string
     */
    public function getActionClassName($module, $actionPath)
    {
        $prefix = $this->pathPrefix ? 'Controller\\' . $this->pathPrefix  : 'Controller';
        return $this->nameBuilder->buildClassName([$module, $prefix, $actionPath]);
    }

    /**
     * Check that request uses https protocol if it should.
     * Function redirects user to correct URL if needed.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $path
     * @return void
     */
    protected function _checkShouldBeSecure(\Magento\Framework\App\RequestInterface $request, $path = '')
    {
        if ($request->getPost()) {
            return;
        }

        if ($this->_shouldBeSecure($path) && !$request->isSecure()) {
            $url = $this->_getCurrentSecureUrl($request);
            if ($this->_shouldRedirectToSecure()) {
                $url = $this->_url->getRedirectUrl($url);
            }

            $this->_responseFactory->create()->setRedirect($url)->sendResponse();
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    protected function _getCurrentSecureUrl($request)
    {
        $alias = $request->getAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS) || $request->getPathInfo();
        return $this->_storeManager->getStore()->getBaseUrl('link', true) . ltrim($alias, '/');
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
        return parse_url(
            $this->_scopeConfig->getValue('web/unsecure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            PHP_URL_SCHEME
        ) === 'https' || $this->_scopeConfig->isSetFlag(
            \Magento\Store\Model\Store::XML_PATH_SECURE_IN_FRONTEND,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && parse_url(
            $this->_scopeConfig->getValue('web/secure/base_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
            PHP_URL_SCHEME
        ) == 'https' && $this->_urlSecurityInfo->isSecure(
            $path
        );
    }
}
