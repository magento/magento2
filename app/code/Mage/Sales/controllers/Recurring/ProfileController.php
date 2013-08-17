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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Recurring profiles view/management controller
 */
class Mage_Sales_Recurring_ProfileController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     * @var Mage_Customer_Model_Session
     */
    protected $_session = null;

    /**
     * Make sure customer is logged in and put it into registry
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        $this->_session = Mage::getSingleton('Mage_Customer_Model_Session');
        if (!$this->_session->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        Mage::register('current_customer', $this->_session->getCustomer());
    }

    /**
     * Profiles listing
     */
    public function indexAction()
    {
        $this->_title($this->__('Recurring Billing Profiles'));
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->renderLayout();
    }

    /**
     * Profile main view
     */
    public function viewAction()
    {
        $this->_viewAction();
    }

    /**
     * Profile history view
     */
// TODO: implement
//    public function historyAction()
//    {
//        $this->_viewAction();
//    }

    /**
     * Profile related orders view
     */
    public function ordersAction()
    {
        $this->_viewAction();
    }

    /**
     * Profile payment gateway info view
     */
// TODO: implement
//    public function vendorAction()
//    {
//        $this->_viewAction();
//    }

    /**
     * Attempt to set profile state
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
            $this->_session->addSuccess($this->__('The profile state has been updated.'));
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_session->addError($this->__('We couldn\'t update the profile.'));
            Mage::logException($e);
        }
        if ($profile) {
            $this->_redirect('*/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Fetch an update with profile
     */
    public function updateProfileAction()
    {
        $profile = null;
        try {
            $profile = $this->_initProfile();
            $profile->fetchUpdate();
            if ($profile->hasDataChanges()) {
                $profile->save();
                $this->_session->addSuccess($this->__('The profile has been updated.'));
            } else {
                $this->_session->addNotice($this->__('The profile has no changes.'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_session->addError($this->__('We couldn\'t update the profile.'));
            Mage::logException($e);
        }
        if ($profile) {
            $this->_redirect('*/*/view', array('profile' => $profile->getId()));
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Generic profile view action
     */
    protected function _viewAction()
    {
        try {
            $profile = $this->_initProfile();
            $this->_title($this->__('Recurring Billing Profiles'))->_title($this->__('Profile #%s', $profile->getReferenceId()));
            $this->loadLayout();
            $this->_initLayoutMessages('Mage_Customer_Model_Session');
            $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('sales/recurring_profile/');
            }
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_session->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
        }
        $this->_redirect('*/*/');
    }

    /**
     * Instantiate current profile and put it into registry
     *
     * @return Mage_Sales_Model_Recurring_Profile
     * @throws Mage_Core_Exception
     */
    protected function _initProfile()
    {
        $profile = Mage::getModel('Mage_Sales_Model_Recurring_Profile')->load($this->getRequest()->getParam('profile'));
        if (!$profile->getId()) {
            Mage::throwException($this->__('We can\'t find the profile you specified.'));
        }
        Mage::register('current_recurring_profile', $profile);
        return $profile;
    }
}
