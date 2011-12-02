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
 * @package     Mage_Persistent
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Persistent Shopping Cart Data Helper
 *
 * @category   Mage
 * @package    Mage_Persistent
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Persistent_Helper_Session extends Mage_Core_Helper_Data
{
    /**
     * Instance of Session Model
     *
     * @var Mage_Persistent_Model_Session
     */
    protected $_sessionModel = null;

    /**
     * Persistent customer
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     * Is "Remember Me" checked
     *
     * @var bool
     */
    protected $_isRememberMeChecked = null;

    /**
     * Get Session model
     *
     * @return Mage_Persistent_Model_Session
     */
    public function getSession()
    {
        if (is_null($this->_sessionModel)) {
            $this->_sessionModel = Mage::getModel('Mage_Persistent_Model_Session');
            $this->_sessionModel->loadByCookieKey();
        }
        return $this->_sessionModel;
    }

    /**
     * Force setting session model
     *
     * @param Mage_Persistent_Model_Session $sessionModel
     * @return Mage_Persistent_Model_Session
     */
    public function setSession($sessionModel)
    {
        $this->_sessionModel = $sessionModel;
        return $this->_sessionModel;
    }

    /**
     * Check whether persistent mode is running
     *
     * @return bool
     */
    public function isPersistent()
    {
        return (bool)$this->getSession()->getId() && Mage::helper('Mage_Persistent_Helper_Data')->isEnabled();
    }

    /**
     * Check if "Remember Me" checked
     *
     * @return bool
     */
    public function isRememberMeChecked()
    {
        if (is_null($this->_isRememberMeChecked)) {
            //Try to get from checkout session
            $isRememberMeChecked = Mage::getSingleton('Mage_Checkout_Model_Session')->getRememberMeChecked();
            if (!is_null($isRememberMeChecked)) {
                $this->_isRememberMeChecked = $isRememberMeChecked;
                Mage::getSingleton('Mage_Checkout_Model_Session')->unsRememberMeChecked();
                return $isRememberMeChecked;
            }

            /** @var $helper Mage_Persistent_Helper_Data */
            $helper = Mage::helper('Mage_Persistent_Helper_Data');
            return $helper->isEnabled() && $helper->isRememberMeEnabled() && $helper->isRememberMeCheckedDefault();
        }

        return (bool)$this->_isRememberMeChecked;
    }

    /**
     * Set "Remember Me" checked or not
     *
     * @param bool $checked
     */
    public function setRememberMeChecked($checked = true)
    {
        $this->_isRememberMeChecked = $checked;
    }

    /**
     * Return persistent customer
     *
     * @return Mage_Customer_Model_Customer|bool
     */
    public function getCustomer()
    {
        if (is_null($this->_customer)) {
            $customerId = $this->getSession()->getCustomerId();
            $this->_customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
        }
        return $this->_customer;
    }
}
