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
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout address matipulation controller
 *
 * @category   Magento
 * @package    Magento_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Checkout\Controller\Multishipping;

class Address extends \Magento\App\Action\Action
{
    /**
     * Retrieve multishipping checkout model
     *
     * @return \Magento\Checkout\Model\Type\Multishipping
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Type\Multishipping');
    }

    /**
     * Retrieve checkout state model
     *
     * @return \Magento\Checkout\Model\Type\Multishipping\State
     */
    protected function _getState()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Type\Multishipping\State');
    }


    /**
     * Create New Shipping address Form
     */
    public function newShippingAction()
    {
        $this->_getState()->setActiveStep(\Magento\Checkout\Model\Type\Multishipping\State::STEP_SELECT_ADDRESSES);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(__('Create Shipping Address'))
                ->setSuccessUrl($this->_url->getUrl('*/*/shippingSaved'))
                ->setErrorUrl($this->_url->getUrl('*/*/*'));

            if ($headBlock = $this->_view->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }

            if ($this->_getCheckout()->getCustomerDefaultShippingAddress()) {
                $addressForm->setBackUrl($this->_url->getUrl('*/multishipping/addresses'));
            } else {
                $addressForm->setBackUrl($this->_url->getUrl('*/cart/'));
            }
        }
        $this->_view->renderLayout();
    }

    public function shippingSavedAction()
    {
        /**
         * if we create first address we need reset emd init checkout
         */
        if (count($this->_getCheckout()->getCustomer()->getAddresses()) == 1) {
            $this->_getCheckout()->reset();
        }
        $this->_redirect('*/multishipping/addresses');
    }

    public function editShippingAction()
    {
        $this->_getState()->setActiveStep(\Magento\Checkout\Model\Type\Multishipping\State::STEP_SHIPPING);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(__('Edit Shipping Address'))
                ->setSuccessUrl($this->_url->getUrl('*/*/editShippingPost', array('id'=>$this->getRequest()->getParam('id'))))
                ->setErrorUrl($this->_url->getUrl('*/*/*'));

            if ($headBlock = $this->_view->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }

            if ($this->_getCheckout()->getCustomerDefaultShippingAddress()) {
                $addressForm->setBackUrl($this->_url->getUrl('*/multishipping/shipping'));
            }
        }
        $this->_view->renderLayout();
    }

    public function editShippingPostAction()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            $this->_objectManager->create('Magento\Checkout\Model\Type\Multishipping')
                ->updateQuoteCustomerShippingAddress($addressId);
        }
        $this->_redirect('*/multishipping/shipping');
    }

    public function selectBillingAction()
    {
        $this->_getState()->setActiveStep(\Magento\Checkout\Model\Type\Multishipping\State::STEP_BILLING);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    public function newBillingAction()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(__('Create Billing Address'))
                ->setSuccessUrl($this->_url->getUrl('*/*/selectBilling'))
                ->setErrorUrl($this->_url->getUrl('*/*/*'))
                ->setBackUrl($this->_url->getUrl('*/*/selectBilling'));

            if ($headBlock = $this->_view->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }
        }
        $this->_view->renderLayout();
    }

    public function editAddressAction()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(__('Edit Address'))
                ->setSuccessUrl($this->_url->getUrl('*/*/selectBilling'))
                ->setErrorUrl($this->_url->getUrl('*/*/*', array('id'=>$this->getRequest()->getParam('id'))))
                ->setBackUrl($this->_url->getUrl('*/*/selectBilling'));

            if ($headBlock = $this->_view->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }
        }
        $this->_view->renderLayout();
    }

    public function editBillingAction()
    {
        $this->_getState()->setActiveStep(
            \Magento\Checkout\Model\Type\Multishipping\State::STEP_BILLING
        );
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(__('Edit Billing Address'))
                ->setSuccessUrl($this->_url->getUrl('*/*/saveBilling', array('id'=>$this->getRequest()->getParam('id'))))
                ->setErrorUrl($this->_url->getUrl('*/*/*', array('id'=>$this->getRequest()->getParam('id'))))
                ->setBackUrl($this->_url->getUrl('*/multishipping/overview'));
            if ($headBlock = $this->_view->getLayout()->getBlock('head')) {
                $headBlock->setTitle($addressForm->getTitle() . ' - ' . $headBlock->getDefaultTitle());
            }
        }
        $this->_view->renderLayout();
    }

    public function setBillingAction()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            $this->_objectManager->create('Magento\Checkout\Model\Type\Multishipping')
                ->setQuoteCustomerBillingAddress($addressId);
        }
        $this->_redirect('*/multishipping/billing');
    }

    public function saveBillingAction()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            $this->_objectManager->create('Magento\Checkout\Model\Type\Multishipping')
                ->setQuoteCustomerBillingAddress($addressId);
        }
        $this->_redirect('*/multishipping/overview');
    }
}
