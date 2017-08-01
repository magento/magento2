<?php
/**
 * Base router
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Router;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Base implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     * @since 2.0.0
     */
    protected $actionFactory;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $actionInterface = \Magento\Framework\App\ActionInterface::class;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_modules = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_dispatchData = [];

    /**
     * List of required request parameters
     * Order sensitive
     * @var string[]
     * @since 2.0.0
     */
    protected $_requiredParams = ['moduleFrontName', 'actionPath', 'actionName'];

    /**
     * @var \Magento\Framework\App\Route\ConfigInterface
     * @since 2.0.0
     */
    protected $_routeConfig;

    /**
     * Url security information.
     *
     * @var \Magento\Framework\Url\SecurityInfoInterface
     * @since 2.0.0
     */
    protected $_urlSecurityInfo;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     * @since 2.0.0
     */
    protected $_responseFactory;

    /**
     * @var \Magento\Framework\App\DefaultPathInterface
     * @since 2.0.0
     */
    protected $_defaultPath;

    /**
     * @var \Magento\Framework\Code\NameBuilder
     * @since 2.0.0
     */
    protected $nameBuilder;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $reservedNames = ['new', 'print', 'switch', 'return'];

    /**
     * Allows to control if we need to enable no route functionality in current router
     *
     * @var bool
     * @since 2.0.0
     */
    protected $applyNoRoute = false;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $pathPrefix = null;

    /**
     * @var \Magento\Framework\App\Router\ActionList
     * @since 2.0.0
     */
    protected $actionList;

    /**
     * @var \Magento\Framework\App\Router\PathConfigInterface
     * @since 2.0.0
     */
    protected $pathConfig;

    /**
     * @param \Magento\Framework\App\Router\ActionList $actionList
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\Route\ConfigInterface $routeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Framework\App\Router\PathConfigInterface $pathConfig
     *
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Router\ActionList $actionList,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Route\ConfigInterface $routeConfig,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \Magento\Framework\App\Router\PathConfigInterface $pathConfig
    ) {
        $this->actionList = $actionList;
        $this->actionFactory = $actionFactory;
        $this->_responseFactory = $responseFactory;
        $this->_defaultPath = $defaultPath;
        $this->_routeConfig = $routeConfig;
        $this->_url = $url;
        $this->nameBuilder = $nameBuilder;
        $this->pathConfig = $pathConfig;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function parseRequest(\Magento\Framework\App\RequestInterface $request)
    {
        $output = [];

        $path = trim($request->getPathInfo(), '/');

        $params = explode('/', $path ? $path : $this->pathConfig->getDefaultPath());
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @return \Magento\Framework\App\ActionInterface|null
     * @since 2.0.0
     */
    protected function getNotFoundAction($currentModuleName)
    {
        if (!$this->applyNoRoute) {
            return null;
        }

        $actionClassName = $this->getActionClassName($currentModuleName, 'noroute');
        if (!$actionClassName || !is_subclass_of($actionClassName, $this->actionInterface)) {
            return null;
        }

        // instantiate action class
        return $this->actionFactory->create($actionClassName);
    }

    /**
     * Create matched controller instance
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $params
     * @return \Magento\Framework\App\ActionInterface|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
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

            $actionInstance = $this->actionFactory->create($actionClassName);
            break;
        }

        if (null == $actionInstance) {
            $actionInstance = $this->getNotFoundAction($currentModuleName);
            if ($actionInstance === null) {
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
     * Build controller class name
     *
     * @param string $module
     * @param string $actionPath
     * @return string
     * @since 2.0.0
     */
    public function getActionClassName($module, $actionPath)
    {
        $prefix = $this->pathPrefix ? 'Controller\\' . $this->pathPrefix : 'Controller';
        return $this->nameBuilder->buildClassName([$module, $prefix, $actionPath]);
    }

    /**
     * Check that request uses https protocol if it should.
     * Function redirects user to correct URL if needed.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param string $path
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     * @since 2.0.0
     */
    protected function _checkShouldBeSecure(\Magento\Framework\App\RequestInterface $request, $path = '')
    {
        if ($request->getPostValue()) {
            return;
        }

        if ($this->pathConfig->shouldBeSecure($path) && !$request->isSecure()) {
            $url = $this->pathConfig->getCurrentSecureUrl($request);
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
     * @since 2.0.0
     */
    protected function _shouldRedirectToSecure()
    {
        return $this->_url->getUseSession();
    }
}
