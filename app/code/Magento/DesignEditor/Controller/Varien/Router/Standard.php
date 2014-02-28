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
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * Routers that must not been matched
     *
     * @var string[]
     */
    protected $_excludedRouters = array('admin', 'vde');

    /**
     * Router list
     *
     * @var \Magento\App\RouterListInterface
     */
    protected $_routerList;

    /**
     * @var \Magento\Core\App\Request\RewriteService
     */
    protected $_urlRewriteService;

    /**
     * @param \Magento\App\ActionFactory $actionFactory
     * @param \Magento\App\DefaultPathInterface $defaultPath
     * @param \Magento\App\ResponseFactory $responseFactory
     * @param \Magento\App\Route\Config $routeConfig
     * @param \Magento\App\State $appState
     * @param \Magento\UrlInterface $url
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\Url\SecurityInfoInterface $urlSecurityInfo
     * @param string $routerId
     * @param \Magento\Code\NameBuilder $nameBuilder
     * @param \Magento\App\RouterListInterface $routerList
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Core\App\Request\RewriteService $urlRewriteService
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\App\ActionFactory $actionFactory,
        \Magento\App\DefaultPathInterface $defaultPath,
        \Magento\App\ResponseFactory $responseFactory,
        \Magento\App\Route\Config $routeConfig,
        \Magento\App\State $appState,
        \Magento\UrlInterface $url,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\Url\SecurityInfoInterface $urlSecurityInfo,
        $routerId,
        \Magento\Code\NameBuilder $nameBuilder,
        \Magento\App\RouterListInterface $routerList,
        \Magento\ObjectManager $objectManager,
        \Magento\Core\App\Request\RewriteService $urlRewriteService
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
        $this->_urlRewriteService = $urlRewriteService;
        $this->_objectManager = $objectManager;
        $this->_routerList = $routerList;
    }

    /**
     * Match provided request and if matched - return corresponding controller
     *
     * @param \Magento\App\RequestInterface $request
     * @return \Magento\App\Action\Action|null
     */
    public function match(\Magento\App\RequestInterface $request)
    {
        // if URL has VDE prefix
        if (!$this->_objectManager->get('Magento\DesignEditor\Helper\Data')->isVdeRequest($request)) {
            return null;
        }

        // user must be logged in admin area
        if (!$this->_objectManager->get('Magento\Backend\Model\Auth\Session')->isLoggedIn()) {
            return null;
        }

        // prepare request to imitate
        $this->_prepareVdeRequest($request);

        // apply rewrites
        $this->_urlRewriteService->applyRewrites($request);

        // match routers
        $controller = null;
        $routers = $this->_getMatchedRouters();
        /** @var $router \Magento\App\Router\AbstractRouter */
        foreach ($routers as $router) {
            /** @var $controller \Magento\App\Action\AbstractAction */
            $controller = $router->match($request);
            if ($controller) {
                $this->_objectManager->get('Magento\DesignEditor\Model\State')
                    ->update(\Magento\Core\Model\App\Area::AREA_FRONTEND, $request);
                break;
            }
        }

        // set inline translation mode
        $this->_objectManager->get('Magento\DesignEditor\Helper\Data')->setTranslationMode($request);

        return $controller;
    }

    /**
     * Modify request path to imitate basic request
     *
     * @param \Magento\App\RequestInterface $request
     * @return $this
     */
    protected function _prepareVdeRequest(\Magento\App\RequestInterface $request)
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
        $routers = $this->_routerList->getRouters();
        foreach (array_keys($routers) as $name) {
            if (in_array($name, $this->_excludedRouters)) {
                unset($routers[$name]);
            }
        }
        return $routers;
    }
}
