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


/**
 * Backend Auth model
 *
 * @category    Mage
 * @package     Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Auth
{
    /**
     * @var Mage_Backend_Model_Auth_StorageInterface
     */
    protected $_authStorage = null;

    /**
     * @var Mage_Backend_Model_Auth_Credential_StorageInterface
     */
    protected $_credentialStorage = null;

    /**
     * @param Mage_Backend_Model_Auth_StorageInterface $authStorage
     * @param Mage_Backend_Model_Auth_Credential_StorageInterface $credentialStorage
     */
    public function __construct(
        Mage_Backend_Model_Auth_StorageInterface $authStorage,
        Mage_Backend_Model_Auth_Credential_StorageInterface $credentialStorage
    ) {
        $this->_authStorage = $authStorage;
        $this->_credentialStorage = $credentialStorage;
    }

    /**
     * Set auth storage if it is instance of Mage_Backend_Model_Auth_StorageInterface
     *
     * @param Mage_Backend_Model_Auth_StorageInterface $storage
     * @return Mage_Backend_Model_Auth
     * @throw Mage_Backend_Model_Auth_Exception if $storage is not correct
     */
    public function setAuthStorage($storage)
    {
        if (!($storage instanceof Mage_Backend_Model_Auth_StorageInterface)) {
            self::throwException('Authentication storage is incorrect.');
        }
        $this->_authStorage = $storage;
        return $this;
    }

    /**
     * Return auth storage.
     * If auth storage was not defined outside - returns default object of auth storage
     *
     * @return Mage_Backend_Model_Auth_StorageInterface
     */
    public function getAuthStorage()
    {
        return $this->_authStorage;
    }

    /**
     * Return current (successfully authenticated) user,
     * an instance of Mage_Backend_Model_Auth_Credential_StorageInterface
     *
     * @return Mage_Backend_Model_Auth_Credential_StorageInterface
     */
    public function getUser()
    {
        return $this->getAuthStorage()->getUser();
    }

    /**
     * Initialize credential storage from configuration
     *
     * @return void
     * @throw Mage_Backend_Model_Auth_Exception if credential storage absent or has not correct configuration
     */
    protected function _initCredentialStorage()
    {
        $areaConfig = Mage::getConfig()->getAreaConfig(Mage::helper('Mage_Backend_Helper_Data')->getAreaCode());
        $storage = Mage::getModel($areaConfig['auth']['credential_storage']);

        if ($storage instanceof Mage_Backend_Model_Auth_Credential_StorageInterface) {
            $this->_credentialStorage = $storage;
            return;
        }

        self::throwException(
            Mage::helper('Mage_Backend_Helper_Data')->__('There are no authentication credential storage.')
        );
    }

    /**
     * Return credential storage object
     *
     * @return null | Mage_Backend_Model_Auth_Credential_StorageInterface
     */
    public function getCredentialStorage()
    {
        return $this->_credentialStorage;
    }

    /**
     * Perform login process
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws Mage_Backend_Model_Auth_Exception if login process was unsuccessful
     */
    public function login($username, $password)
    {
        if (empty($username) || empty($password)) {
            self::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid User Name or Password.'));
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->login($username, $password);
            if ($this->getCredentialStorage()->getId()) {

                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();

                Mage::dispatchEvent('backend_auth_user_login_success', array('user' => $this->getCredentialStorage()));
            }

            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid User Name or Password.'));
            }

        } catch (Mage_Backend_Model_Auth_Plugin_Exception $e) {
            Mage::dispatchEvent('backend_auth_user_login_failed', array('user_name' => $username, 'exception' => $e));
            throw $e;
        } catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('backend_auth_user_login_failed', array('user_name' => $username, 'exception' => $e));
            self::throwException(Mage::helper('Mage_Backend_Helper_Data')->__('Invalid User Name or Password.'));
        }
    }

    /**
     * Perform logout process
     *
     * @return void
     */
    public function logout()
    {
        $this->getAuthStorage()->processLogout();
        Mage::dispatchEvent('admin_session_user_logout');
    }

    /**
     * Check if current user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getAuthStorage()->isLoggedIn();
    }

    /**
     * Throws specific Backend Authentication Exception
     *
     * @static
     * @param string $msg
     * @param string $code
     * @throws Mage_Backend_Model_Auth_Exception
     */
    public static function throwException($msg = null, $code = null)
    {
        if (is_null($msg)) {
            $msg = Mage::helper('Mage_Backend_Helper_Data')->__('Authentication error occurred.');
        }
        throw new Mage_Backend_Model_Auth_Exception($msg, $code);
    }
}
