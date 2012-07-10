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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
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

    public function __construct(array $data = array())
    {
        parent::__construct($data);
        $this->_startupMenuItemId = isset($data['startupMenuItemId']) ?
            $data['startupMenuItemId'] :
            Mage::getStoreConfig(self::XML_PATH_STARTUP_MENU_ITEM);

        $this->_menu = isset($data['menu']) ? $data['menu'] : null;
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

        $_route = $this->getRouteName() ? $this->getRouteName() : '*';
        $_controller = $this->getControllerName() ? $this->getControllerName() : $this->getDefaultControllerName();
        $_action = $this->getActionName() ? $this->getActionName() : $this->getDefaultActionName();

        if ($cacheSecretKey) {
            $secret = array(self::SECRET_KEY_PARAM_NAME => "\${$_controller}/{$_action}\$");
        }
        else {
            $secret = array(self::SECRET_KEY_PARAM_NAME => $this->getSecretKey($_controller, $_action));
        }
        if (is_array($routeParams)) {
            $routeParams = array_merge($secret, $routeParams);
        } else {
            $routeParams = $secret;
        }
        if (is_array($this->getRouteParams())) {
            $routeParams = array_merge($this->getRouteParams(), $routeParams);
        }

        return parent::getUrl("{$_route}/{$_controller}/{$_action}", $routeParams);
    }

    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($controller = null, $action = null)
    {
        $salt = Mage::getSingleton('Mage_Core_Model_Session')->getFormKey();

        $request = $this->getRequest();
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

        $secret = $controller . $action . $salt;
        return Mage::helper('Mage_Core_Helper_Data')->getHash($secret);
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
        $aclResource = 'admin/' . $this->_startupMenuItemId;
        if ($this->_getSession()->isAllowed($aclResource)) {
            $menuItem = $this->_getMenu()->get($this->_startupMenuItemId);
            if ($menuItem && $menuItem->getAction()) {
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
}
