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
 * @package     Mage_Admin
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Auth session model
 *
 * @category    Mage
 * @package     Mage_Admin
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Admin_Model_Session extends Mage_Core_Model_Session_Abstract
{
    const XML_PATH_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Whether it is the first page after successfull login
     *
     * @var boolean
     */
    protected $_isFirstPageAfterLogin;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        $this->init('admin');
    }

    /**
     * Pull out information from session whether there is currently the first page after log in
     *
     * The idea is to set this value on login(), then redirect happens,
     * after that on next request the value is grabbed once the session is initialized
     * Since the session is used as a singleton, the value will be in $_isFirstPageAfterLogin until the end of request,
     * unless it is reset intentionally from somewhere
     *
     * @param string $namespace
     * @param string $sessionName
     * @return Mage_Admin_Model_Session
     * @see self::login()
     */
    public function init($namespace, $sessionName = null)
    {
        parent::init($namespace, $sessionName);
        $this->isFirstPageAfterLogin();
        return $this;
    }

    /**
     * Try to login user in admin. Possible results:
     * - Mage_Admin_Model_User - user logged in and appropriate model is loaded
     * - false - user not logged in
     *
     * @param  string $username
     * @param  string $password
     * @return Mage_Admin_Model_User|bool
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            return false;
        }

        try {
            /** @var $user Mage_Admin_Model_User */
            $user = Mage::getModel('Mage_Admin_Model_User');
            $user->login($username, $password);
            if (!$user->getId()) {
                Mage::throwException(Mage::helper('Mage_Adminhtml_Helper_Data')->__('Invalid User Name or Password.'));
            }

            $this->renewSession();

            if (Mage::getSingleton('Mage_Adminhtml_Model_Url')->useSecretKey()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Url')->renewSecretUrls();
            }
            $this->setIsFirstPageAfterLogin(true);
            $this->setUser($user);
            $this->setAcl(Mage::getResourceModel('Mage_Admin_Model_Resource_Acl')->loadAcl());
            $this->setUpdatedAt(time());

            Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));

            return $user;
        } catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('admin_session_user_login_failed',
                array('user_name' => $username, 'exception' => $e));
            return false;
        }
    }

    /**
     * Log out the user from the admin
     */
    public function logout()
    {
        $this->unsetAll();
        $this->getCookie()->delete($this->getSessionName());
        Mage::dispatchEvent('admin_session_user_logout');
    }

    /**
     * Refresh ACL resources stored in session
     *
     * @param  Mage_Admin_Model_User $user
     * @return Mage_Admin_Model_Session
     */
    public function refreshAcl($user = null)
    {
        if (is_null($user)) {
            $user = $this->getUser();
        }
        if (!$user) {
            return $this;
        }
        if (!$this->getAcl() || $user->getReloadAclFlag()) {
            $this->setAcl(Mage::getResourceModel('Mage_Admin_Model_Resource_Acl')->loadAcl());
        }
        if ($user->getReloadAclFlag()) {
            $user->unsetData('password');
            $user->setReloadAclFlag('0')->save();
        }
        return $this;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('admin/catalog')
     * Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('catalog')
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        $user = $this->getUser();
        $acl = $this->getAcl();

        if ($user && $acl) {
            if (!preg_match('/^admin/', $resource)) {
                $resource = 'admin/' . $resource;
            }

            try {
                return $acl->isAllowed($user->getAclRole(), $resource, $privilege);
            } catch (Exception $e) {
                try {
                    if (!$acl->has($resource)) {
                        return $acl->isAllowed($user->getAclRole(), null, $privilege);
                    }
                } catch (Exception $e) { }
            }
        }
        return false;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        $lifetime = Mage::getStoreConfig(self::XML_PATH_SESSION_LIFETIME);
        $currentTime = time();

        /* Validate admin session lifetime that should be more than 60 seconds */
        if ($lifetime >= 60 && ($this->getUpdatedAt() < $currentTime - $lifetime)) {
            return false;
        }

        if ($this->getUser() && $this->getUser()->getId()) {
            $this->setUpdatedAt($currentTime);
            return true;
        }
        return false;
    }

    /**
     * Check if it is the first page after successfull login
     *
     * @return boolean
     */
    public function isFirstPageAfterLogin()
    {
        if (is_null($this->_isFirstPageAfterLogin)) {
            $this->_isFirstPageAfterLogin = $this->getData('is_first_visit', true);
        }
        return $this->_isFirstPageAfterLogin;
    }

    /**
     * Setter whether the current/next page should be treated as first page after login
     *
     * @param bool $value
     * @return Mage_Admin_Model_Session
     */
    public function setIsFirstPageAfterLogin($value)
    {
        $this->_isFirstPageAfterLogin = (bool)$value;
        return $this->setIsFirstVisit($this->_isFirstPageAfterLogin);
    }
}
