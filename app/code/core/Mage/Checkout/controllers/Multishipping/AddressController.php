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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout address matipulation controller
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Multishipping_AddressController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve multishipping checkout model
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Type_Multishipping');
    }

    /**
     * Retrieve checkout state model
     *
     * @return Mage_Checkot_Model_Type_Multishipping_State
     */
    protected function _getState()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Type_Multishipping_State');
    }


    /**
     * Create New Shipping address Form
     */
    public function newShippingAction()
    {
        $this->_getState()->setActiveStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES);
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        if ($addressForm = $this->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(Mage::helper('Mage_Checkout_Helper_Data')->__('Create Shipping Address'))
                ->setSuccessUrl(Mage::getUrl('*/*/shippingSaved'))
                ->setErrorUrl(Mage::getUrl('*/*/*'));

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }

            if ($this->_getCheckout()->getCustomerDefaultShippingAddress()) {
                $addressForm->setBackUrl(Mage::getUrl('*/multishipping/addresses'));
            }
            else {
                $addressForm->setBackUrl(Mage::getUrl('*/cart/'));
            }
        }
        $this->renderLayout();
    }

    public function shippingSavedAction()
    {
        /**
         * if we create first address we need reset emd init checkout
         */
        if (count($this->_getCheckout()->getCustomer()->getAddresses()) == 1) {
            $this->_getCheckout()->reset();
        }
        $this->_redirect('*/multishipping/');
    }

    public function editShippingAction()
    {
        $this->_getState()->setActiveStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING);
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        if ($addressForm = $this->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(Mage::helper('Mage_Checkout_Helper_Data')->__('Edit Shipping Address'))
                ->setSuccessUrl(Mage::getUrl('*/*/editShippingPost', array('id'=>$this->getRequest()->getParam('id'))))
                ->setErrorUrl(Mage::getUrl('*/*/*'));

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }

            if ($this->_getCheckout()->getCustomerDefaultShippingAddress()) {
                $addressForm->setBackUrl(Mage::getUrl('*/multishipping/shipping'));
            }
        }
        $this->renderLayout();
    }

    public function editShippingPostAction()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            Mage::getModel('Mage_Checkout_Model_Type_Multishipping')
                ->updateQuoteCustomerShippingAddress($addressId);
        }
        $this->_redirect('*/multishipping/shipping');
    }

    public function selectBillingAction()
    {
        $this->_getState()->setActiveStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING);
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Checkout_Model_Session');
        $this->renderLayout();
    }

    public function newBillingAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        if ($addressForm = $this->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(Mage::helper('Mage_Checkout_Helper_Data')->__('Create Billing Address'))
                ->setSuccessUrl(Mage::getUrl('*/*/selectBilling'))
                ->setErrorUrl(Mage::getUrl('*/*/*'))
                ->setBackUrl(Mage::getUrl('*/*/selectBilling'));

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }
        }
        $this->renderLayout();
    }

    public function editAddressAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        if ($addressForm = $this->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(Mage::helper('Mage_Checkout_Helper_Data')->__('Edit Address'))
                ->setSuccessUrl(Mage::getUrl('*/*/selectBilling'))
                ->setErrorUrl(Mage::getUrl('*/*/*', array('id'=>$this->getRequest()->getParam('id'))))
                ->setBackUrl(Mage::getUrl('*/*/selectBilling'));

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }
        }
        $this->renderLayout();
    }

    public function editBillingAction()
    {
        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
        );
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        if ($addressForm = $this->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(Mage::helper('Mage_Checkout_Helper_Data')->__('Edit Billing Address'))
                ->setSuccessUrl(Mage::getUrl('*/*/saveBilling', array('id'=>$this->getRequest()->getParam('id'))))
                ->setErrorUrl(Mage::getUrl('*/*/*', array('id'=>$this->getRequest()->getParam('id'))))
                ->setBackUrl(Mage::getUrl('*/multishipping/overview'));
            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }
        }
        $this->renderLayout();
    }

    public function setBillingAction()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            Mage::getModel('Mage_Checkout_Model_Type_Multishipping')
                ->setQuoteCustomerBillingAddress($addressId);
        }
        $this->_redirect('*/multishipping/billing');
    }

    public function saveBillingAction()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            Mage::getModel('Mage_Checkout_Model_Type_Multishipping')
                ->setQuoteCustomerBillingAddress($addressId);
        }
        $this->_redirect('*/multishipping/overview');
    }
}
