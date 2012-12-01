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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Url extends Mage_Core_Model_Url
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
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_session;

    /**
     * @var Mage_Backend_Model_Menu
     */
    protected $_menu;

    /**
     * Startup page url from config
     * @var string
     */
    protected $_startupMenuItemId;

    /**
     * @var Mage_Backend_Helper_Data
     */
    protected $_backendHelper;

    /**
     * @var Mage_Core_Helper_Data
     */
    protected $_coreHelper;

    /**
     * @var Mage_Core_Model_Session
     */
    protected $_coreSession;

    /**
     * @var array
     */
    protected $_routes;

    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->_startupMenuItemId = isset($data['startupMenuItemId']) ?
            $data['startupMenuItemId'] :
            Mage::getStoreConfig(self::XML_PATH_STARTUP_MENU_ITEM);

        $this->_menu = isset($data['menu']) ? $data['menu'] : null;

        $this->_backendHelper = isset($data['backendHelper']) ?
            $data['backendHelper'] :
            Mage::helper('Mage_Backend_Helper_Data');

        if (false == ($this->_backendHelper instanceof Mage_Backend_Helper_Data)) {
            throw new InvalidArgumentException('Backend helper is corrupted');
        }

        $this->_coreSession = isset($data['coreSession']) ?
            $data['coreSession'] :
            Mage::getSingleton('Mage_Core_Model_Session');

        $this->_coreHelper = isset($data['coreHelper']) ?
            $data['coreHelper'] :
            Mage::helper('Mage_Core_Helper_Data');

        $this->_routes = isset($data['routes']) ?
            $data['routes'] :
            array();
    }

    /**
     * Retrieve is secure mode for ULR logic
     *
     * @return bool
     */
    public function getSecure()
    {
        if ($this->hasData('secure_is_forced')) {
            return $this->getData('secure');
        }
        return Mage::getStoreConfigFlag('web/secure/use_in_adminhtml');
    }

    /**
     * Force strip secret key param if _nosecret param specified
     *
     * @param array $data
     * @param bool $unsetOldParams
     * @return Mage_Core_Model_Url
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

        $routeName = $this->getRouteName() ? $this->getRouteName() : '*';
        $controllerName = $this->getControllerName() ? $this->getControllerName() : $this->getDefaultControllerName();
        $actionName = $this->getActionName() ? $this->getActionName() : $this->getDefaultActionName();

        if ($cacheSecretKey) {
            $secret = array(self::SECRET_KEY_PARAM_NAME => "\${$routeName}/{$controllerName}/{$actionName}\$");
        }
        else {
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
        return $this->_coreHelper->getHash($secret);
    }

    /**
     * Return secret key settings flag
     *
     * @return boolean
     */
    public function useSecretKey()
    {
        return Mage::getStoreConfigFlag('admin/security/use_form_key') && !$this->getNoSecret();
    }

    /**
     * Enable secret key using
     *
     * @return Mage_Backend_Model_Url
     */
    public function turnOnSecretKey()
    {
        $this->setNoSecret(false);
        return $this;
    }

    /**
     * Disable secret key using
     *
     * @return Mage_Backend_Model_Url
     */
    public function turnOffSecretKey()
    {
        $this->setNoSecret(true);
        return $this;
    }

    /**
     * Refresh admin menu cache etc.
     *
     * @return Mage_Backend_Model_Url
     */
    public function renewSecretUrls()
    {
        Mage::app()->cleanCache(array(Mage_Backend_Block_Menu::CACHE_TAGS));
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
        /* @var $menu Mage_Backend_Model_Menu_Item */
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
     * @return Mage_Backend_Model_Menu
     */
    protected function _getMenu()
    {
        if (is_null($this->_menu)) {
            $this->_menu = Mage::getSingleton('Mage_Backend_Model_Menu_Config')->getMenu();
        }
        return $this->_menu;
    }

    /**
     * Set custom auth session
     *
     * @param Mage_Backend_Model_Auth_Session $session
     * @return Mage_Backend_Model_Url
     */
    public function setSession(Mage_Backend_Model_Auth_Session $session)
    {
        $this->_session = $session;
        return $this;
    }

    /**
     * Retrieve auth session
     *
     * @return Mage_Backend_Model_Auth_Session
     */
    protected function _getSession()
    {
        if ($this->_session == null) {
            $this->_session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        }
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
