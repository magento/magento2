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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model;

use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Menu;

/**
 * Class \Magento\Backend\Model\Url
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Url extends \Magento\Core\Model\Url
{
    /**
     * Secret key query param name
     */
    const SECRET_KEY_PARAM_NAME = 'key';

    /**
     * xpath to startup page in configuration
     */
    const XML_PATH_STARTUP_MENU_ITEM = 'admin/startup/menu_item_id';

    /**
     * Authentication session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menu;

    /**
     * Startup page url from config
     * @var string
     */
    protected $_startupMenuItemId;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backendHelper;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_coreSession;

    /**
     * Menu config
     *
     * @var \Magento\Backend\Model\Menu\Config
     */
    protected $_menuConfig;

    /**
     * @var \Magento\Core\Model\CacheInterface
     */
    protected $_cache;

    /**
     * @param \Magento\App\RouterListInterface $routerList
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Core\Model\Url\SecurityInfoInterface $securityInfo
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Core\Model\Session $session
     * @param Menu\Config $menuConfig
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Model\App $app
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\CacheInterface $cache
     * @param Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\App\RouterListInterface $routerList,
        \Magento\App\RequestInterface $request,
        \Magento\Core\Model\Url\SecurityInfoInterface $securityInfo,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Core\Model\Session $session,
        \Magento\Backend\Model\Menu\Config $menuConfig,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Model\App $app,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\CacheInterface $cache,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = array()
    ) {
        parent::__construct(
            $routerList, $request, $securityInfo, $coreStoreConfig, $coreData, $app, $storeManager, $session, $data);
        $this->_startupMenuItemId = $coreStoreConfig->getConfig(self::XML_PATH_STARTUP_MENU_ITEM);
        $this->_backendHelper = $backendHelper;
        $this->_coreSession = $session;
        $this->_menuConfig = $menuConfig;
        $this->_cache = $cache;
        $this->_session = $authSession;
    }

    /**
     * Retrieve is secure mode for ULR logic
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->hasData('secure_is_forced')) {
            return $this->getData('secure');
        }
        return $this->_coreStoreConfig->getConfigFlag('web/secure/use_in_adminhtml');
    }

    /**
     * Force strip secret key param if _nosecret param specified
     *
     * @param array $data
     * @param bool $unsetOldParams
     * @return \Magento\Backend\Model\Url
     */
    public function setRouteParams(array $data, $unsetOldParams=true)
    {
        if (isset($data['_nosecret'])) {
            $this->setNoSecret(true);
            unset($data['_nosecret']);
        } else {
            $this->setNoSecret(false);
        }

        return parent::setRouteParams($data, $unsetOldParams);
    }

    /**
     * Custom logic to retrieve Urls
     *
     * @param string $routePath
     * @param array $routeParams
     * @return string
     */
    public function getUrl($routePath=null, $routeParams=null)
    {
        $cacheSecretKey = false;
        if (is_array($routeParams) && isset($routeParams['_cache_secret_key'])) {
            unset($routeParams['_cache_secret_key']);
            $cacheSecretKey = true;
        }

        $result = parent::getUrl($routePath, $routeParams);
        if (!$this->useSecretKey()) {
            return $result;
        }

        $routeName = $this->getRouteName('*');
        $controllerName = $this->getControllerName($this->getDefaultControllerName());
        $actionName = $this->getActionName($this->getDefaultActionName());

        if ($cacheSecretKey) {
            $secret = array(self::SECRET_KEY_PARAM_NAME => "\${$routeName}/{$controllerName}/{$actionName}\$");
        } else {
            $secret = array(
                self::SECRET_KEY_PARAM_NAME => $this->getSecretKey($routeName, $controllerName, $actionName)
            );
        }
        if (is_array($routeParams)) {
            $routeParams = array_merge($secret, $routeParams);
        } else {
            $routeParams = $secret;
        }
        if (is_array($this->getRouteParams())) {
            $routeParams = array_merge($this->getRouteParams(), $routeParams);
        }

        return parent::getUrl("{$routeName}/{$controllerName}/{$actionName}", $routeParams);
    }

    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($routeName = null, $controller = null, $action = null)
    {
        $salt = $this->_coreSession->getFormKey();
        $request = $this->getRequest();

        if (!$routeName) {
            if ($request->getBeforeForwardInfo('route_name') !== null) {
                $routeName = $request->getBeforeForwardInfo('route_name');
            } else {
                $routeName = $request->getRouteName();
            }
        }

        if (!$controller) {
            if ($request->getBeforeForwardInfo('controller_name') !== null) {
                $controller = $request->getBeforeForwardInfo('controller_name');
            } else {
                $controller = $request->getControllerName();
            }
        }

        if (!$action) {
            if ($request->getBeforeForwardInfo('action_name') !== null) {
                $action = $request->getBeforeForwardInfo('action_name');
            } else {
                $action = $request->getActionName();
            }
        }

        $secret = $routeName . $controller . $action . $salt;
        return $this->_coreData->getHash($secret);
    }

    /**
     * Return secret key settings flag
     *
     * @return boolean
     */
    public function useSecretKey()
    {
        return $this->_coreStoreConfig->getConfigFlag('admin/security/use_form_key') && !$this->getNoSecret();
    }

    /**
     * Enable secret key using
     *
     * @return \Magento\Backend\Model\Url
     */
    public function turnOnSecretKey()
    {
        $this->setNoSecret(false);
        return $this;
    }

    /**
     * Disable secret key using
     *
     * @return \Magento\Backend\Model\Url
     */
    public function turnOffSecretKey()
    {
        $this->setNoSecret(true);
        return $this;
    }

    /**
     * Refresh admin menu cache etc.
     *
     * @return \Magento\Backend\Model\Url
     */
    public function renewSecretUrls()
    {
        $this->_cache->clean(array(\Magento\Backend\Block\Menu::CACHE_TAGS));
    }

    /**
     * Find admin start page url
     *
     * @return string
     */
    public function getStartupPageUrl()
    {
        $menuItem = $this->_getMenu()->get($this->_startupMenuItemId);
        if (!is_null($menuItem)) {
            if ($menuItem->isAllowed() && $menuItem->getAction()) {
                return $menuItem->getAction();
            }
        }
        return $this->findFirstAvailableMenu();
    }

    /**
     * Find first menu item that user is able to access
     *
     * @return string
     */
    public function findFirstAvailableMenu()
    {
        /* @var $menu \Magento\Backend\Model\Menu\Item */
        $menu = $this->_getMenu();
        $item = $menu->getFirstAvailable();
        $action = $item ? $item->getAction() : null;
        if (!$item) {
            $user = $this->_getSession()->getUser();
            if ($user) {
                $user->setHasAvailableResources(false);
            }
            $action = '*/*/denied';
        }
        return $action;

    }

    /**
     * Get Menu model
     *
     * @return \Magento\Backend\Model\Menu
     */
    protected function _getMenu()
    {
        if (is_null($this->_menu)) {
            $this->_menu = $this->_menuConfig->getMenu();
        }
        return $this->_menu;
    }

    /**
     * Set custom auth session
     *
     * @param \Magento\Backend\Model\Auth\Session $session
     * @return \Magento\Backend\Model\Url
     */
    public function setSession(\Magento\Backend\Model\Auth\Session $session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Retrieve auth session
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }


    /**
     * Return backend area front name, defined in configuration
     *
     * @return string
     */
    public function getAreaFrontName()
    {
        if (!$this->_getData('area_front_name')) {
            $this->setData('area_front_name', $this->_backendHelper->getAreaFrontName());
        }

        return $this->_getData('area_front_name');
    }

    /**
     * Retrieve action path.
     * Add backend area front name as a prefix to action path
     *
     * @return string
     */
    public function getActionPath()
    {
        $path = parent::getActionPath();
        if ($path) {
            if ($this->getAreaFrontName()) {
                $path = $this->getAreaFrontName() . '/' . $path;
            }
        }

        return $path;
    }
}
