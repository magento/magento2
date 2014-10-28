<?php
/**
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
namespace Magento\DesignEditor\Controller\Varien\Router;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Standard extends \Magento\Core\App\Router\Base
{
    /**
     * Routers that must not been matched
     *
     * @var string[]
     */
    protected $_excludedRouters = array('admin', 'vde');

    /**
     * Router list
     *
     * @var \Magento\Framework\App\RouterListInterface
     */
    protected $_routerList;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_designEditorHelper;

    /**
     * @var \Magento\DesignEditor\Model\State
     */
    protected $_state;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * @param \Magento\Framework\App\Router\ActionList $actionList
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\App\Route\Config $routeConfig
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\Framework\Code\NameBuilder $nameBuilder
     * @param \Magento\Framework\App\RouterListInterface $routerList
     * @param \Magento\DesignEditor\Helper\Data $designEditorHelper
     * @param \Magento\DesignEditor\Model\State $designEditorState
     * @param \Magento\Backend\Model\Auth\Session $session
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Router\ActionList $actionList,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\App\Route\Config $routeConfig,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Url\SecurityInfoInterface $urlSecurityInfo,
        $routerId,
        \Magento\Framework\Code\NameBuilder $nameBuilder,
        \Magento\Framework\App\RouterListInterface $routerList,
        \Magento\DesignEditor\Helper\Data $designEditorHelper,
        \Magento\DesignEditor\Model\State $designEditorState,
        \Magento\Backend\Model\Auth\Session $session
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
        $this->_routerList = $routerList;
        $this->_designEditorHelper = $designEditorHelper;
        $this->_state = $designEditorState;
        $this->_session = $session;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\Action\Action|null
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        // if URL has VDE prefix
        if (!$this->_designEditorHelper->isVdeRequest($request)) {
            return null;
        }

        // user must be logged in admin area
        if (!$this->_session->isLoggedIn()) {
            return null;
        }

        // prepare request to imitate
        $this->_prepareVdeRequest($request);
        /**
         * Deprecated line of code was here which should be adopted if needed:
         * $this->_urlRewriteService->applyRewrites($request);
         */

        // match routers
        $controller = null;
        $routers = $this->_getMatchedRouters();
        /** @var $router \Magento\Framework\App\RouterInterface */
        foreach ($routers as $router) {
            /** @var $controller \Magento\Framework\App\Action\AbstractAction */
            $controller = $router->match($request);
            if ($controller) {
                $this->_state->update(\Magento\Framework\App\Area::AREA_FRONTEND, $request);
                break;
            }
        }

        // set inline translation mode
        $this->_designEditorHelper->setTranslationMode($request);

        return $controller;
    }

    /**
     * Modify request path to imitate basic request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    protected function _prepareVdeRequest(\Magento\Framework\App\RequestInterface $request)
    {
        list($vdeFrontName, $designMode, $themeId) = explode('/', trim($request->getPathInfo(), '/'));
        $request->setAlias('editorMode', $designMode);
        $request->setAlias('themeId', (int)$themeId);
        $vdePath = implode('/', array($vdeFrontName, $designMode, $themeId));
        $noVdePath = substr($request->getPathInfo(), strlen($vdePath) + 1) ?: '/';
        $request->setPathInfo($noVdePath);
        return $this;
    }

    /**
     * Returns list of routers that must been tried to match
     *
     * @return array
     */
    protected function _getMatchedRouters()
    {
        $routers = [];
        foreach ($this->_routerList as $router) {
            $name = $this->_routerList->key();
            if (!in_array($name, $this->_excludedRouters)) {
                $routers[$name] = $router;
            }
        }
        return $routers;
    }
}
