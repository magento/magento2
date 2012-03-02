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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profiles view/management controller
 *
 * TODO: implement ACL restrictions
 */
class Mage_Adminhtml_Sales_Recurring_ProfileController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Recurring profiles list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title(Mage::helper('Mage_Sales_Helper_Data')->__('Sales'))->_title(Mage::helper('Mage_Sales_Helper_Data')->__('Recurring Profiles'))
            ->loadLayout()
            ->_setActiveMenu('sales/recurring_profile')
            ->renderLayout();
        return $this;
    }

    /**
     * View recurring profile detales
     */
    public function viewAction()
    {
        try {
            $this->_title(Mage::helper('Mage_Sales_Helper_Data')->__('Sales'))->_title(Mage::helper('Mage_Sales_Helper_Data')->__('Recurring Profiles'));
            $profile = $this->_initProfile();
            $this->loadLayout()
                ->_setActiveMenu('sales/recurring_profile')
                ->_title(Mage::helper('Mage_Sales_Helper_Data')->__('Profile #%s', $profile->getReferenceId()))
                ->renderLayout()
            ;
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Profiles ajax grid
     */
    public function gridAction()
    {
        try {
            $this->loadLayout()->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Profile orders ajax grid
     */
    public function ordersAction()
    {
        try {
            $this->_initProfile();
            $this->loadLayout()->renderLayout();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->norouteAction();
        }
    }

    /**
     * Profile state updater action
     */
    public function updateStateAction()
    {
        $profile = null;
        try {
            $profile = $this->_initProfile();

            switch ($this->getRequest()->getParam('action')) {
                case 'cancel':
                    $profile->cancel();
                    break;
                case 'suspend':
                    $profile->suspend();
                    break;
                case 'activate':
                    $profile->activate();
                    break;
            }
            $this->_getSession()->addSuccess(Mage::helper('Mage_Sales_Helper_Data')->__('The profile state has been updated.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('Mage_Sales_Helper_Data')->__('Failed to update the profile.'));
            Mage::logException($e);
        }
        if ($profile) {
            $this->_redirect('*/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Profile information updater action
     */
    public function updateProfileAction()
    {
        $profile = null;
        try {
            $profile = $this->_initProfile();
            $profile->fetchUpdate();
            if ($profile->hasDataChanges()) {
                $profile->save();
                $this->_getSession()->addSuccess($this->__('The profile has been updated.'));
            } else {
                $this->_getSession()->addNotice($this->__('The profile has no changes.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Failed to update the profile.'));
            Mage::logException($e);
        }
        if ($profile) {
            $this->_redirect('*/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Cutomer billing agreements ajax action
     *
     */
    public function customerGridAction()
    {
        $this->_initCustomer();
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * Initialize customer by ID specified in request
     *
     * @return Mage_Adminhtml_Sales_Billing_AgreementController
     */
    protected function _initCustomer()
    {
        $customerId = (int) $this->getRequest()->getParam('id');
        $customer = Mage::getModel('Mage_Customer_Model_Customer');

        if ($customerId) {
            $customer->load($customerId);
        }

        Mage::register('current_customer', $customer);
        return $this;
    }

    /**
     * Load/set profile
     *
     * @return Mage_Sales_Model_Recurring_Profile
     */
    protected function _initProfile()
    {
        $profile = Mage::getModel('Mage_Sales_Model_Recurring_Profile')->load($this->getRequest()->getParam('profile'));
        if (!$profile->getId()) {
            Mage::throwException($this->__('Specified profile does not exist.'));
        }
        Mage::register('current_recurring_profile', $profile);
        return $profile;
    }
}
