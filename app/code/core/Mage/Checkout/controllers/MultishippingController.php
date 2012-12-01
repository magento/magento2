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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Multishipping checkout controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_MultishippingController extends Mage_Checkout_Controller_Action
{
    /**
     * Retrieve checkout model
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
     * @return Mage_Checkout_Model_Type_Multishipping_State
     */
    protected function _getState()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Type_Multishipping_State');
    }

    /**
     * Retrieve checkout url heler
     *
     * @return Mage_Checkout_Helper_Url
     */
    protected function _getHelper()
    {
        return Mage::helper('Mage_Checkout_Helper_Url');
    }

    /**
     * Retrieve checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session');
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     *
     * @return Mage_Checkout_MultishippingController
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->getFlag('', 'redirectLogin')) {
            return $this;
        }

        $action = $this->getRequest()->getActionName();

        $checkoutSessionQuote = $this->_getCheckoutSession()->getQuote();
        /**
         * Catch index action call to set some flags before checkout/type_multishipping model initialization
         */
        if ($action == 'index') {
            $checkoutSessionQuote->setIsMultiShipping(true);
            $this->_getCheckoutSession()->setCheckoutState(
                Mage_Checkout_Model_Session::CHECKOUT_STATE_BEGIN
            );
        } elseif (!$checkoutSessionQuote->getIsMultiShipping() &&
            !in_array($action, array('login', 'register', 'success'))
        ) {
            $this->_redirect('*/*/index');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this;
        }

        if (!in_array($action, array('login', 'register'))) {
            if (!Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this, $this->_getHelper()->getMSLoginUrl())) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            }

            if (!Mage::helper('Mage_Checkout_Helper_Data')->isMultishippingCheckoutAvailable()) {
                $error = $this->_getCheckout()->getMinimumAmountError();
                $this->_getCheckoutSession()->addError($error);
                $this->_redirectUrl($this->_getHelper()->getCartUrl());
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                return $this;
            }
        }

        if (!$this->_preDispatchValidateCustomer()) {
            return $this;
        }

        if ($this->_getCheckoutSession()->getCartWasUpdated(true) &&
            !in_array($action, array('index', 'login', 'register', 'addresses', 'success'))
        ) {
            $this->_redirectUrl($this->_getHelper()->getCartUrl());
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }

        if ($action == 'success' && $this->_getCheckout()->getCheckoutSession()->getDisplaySuccess(true)) {
            return $this;
        }

        $quote = $this->_getCheckout()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || $quote->isVirtual()) {
            $this->_redirectUrl($this->_getHelper()->getCartUrl());
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return;
        }

        return $this;
    }

    /**
     * Index action of Multishipping checkout
     */
    public function indexAction()
    {
        $this->_getCheckoutSession()->setCartWasUpdated(false);
        $this->_redirect('*/*/addresses');
    }

    /**
     * Multishipping checkout login page
     */
    public function loginAction()
    {
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');

        // set account create url
        if ($loginForm = $this->getLayout()->getBlock('customer_form_login')) {
            $loginForm->setCreateAccountUrl($this->_getHelper()->getMSRegisterUrl());
        }
        $this->renderLayout();
    }

    /**
     * Multishipping checkout login page
     */
    public function registerAction()
    {
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            $this->_redirectUrl($this->_getHelper()->getMSCheckoutUrl());
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');

        if ($registerForm = $this->getLayout()->getBlock('customer_form_register')) {
            $registerForm->setShowAddressFields(true)
                ->setBackUrl($this->_getHelper()->getMSLoginUrl())
                ->setSuccessUrl($this->_getHelper()->getMSShippingAddressSavedUrl())
                ->setErrorUrl($this->_getHelper()->getCurrentUrl());
        }

        $this->renderLayout();
    }

    /**
     * Multishipping checkout select address page
     */
    public function addressesAction()
    {
        // If customer do not have addresses
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/multishipping_address/newShipping');
            return;
        }

        $this->_getState()->unsCompleteStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
        );

        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES
        );
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $message = $this->_getCheckout()->getMinimumAmountDescription();
            $this->_getCheckout()->getCheckoutSession()->addNotice($message);
        }
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Checkout_Model_Session');
        $this->renderLayout();
    }

    /**
     * Multishipping checkout process posted addresses
     */
    public function addressesPostAction()
    {
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/multishipping_address/newShipping');
            return;
        }
        try {
            if ($this->getRequest()->getParam('continue', false)) {
                $this->_getCheckout()->setCollectRatesFlag(true);
                $this->_getState()->setActiveStep(
                    Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
                );
                $this->_getState()->setCompleteStep(
                    Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES
                );
                $this->_redirect('*/*/shipping');
            }
            elseif ($this->getRequest()->getParam('new_address')) {
                $this->_redirect('*/multishipping_address/newShipping');
            }
            else {
                $this->_redirect('*/*/addresses');
            }
            if ($shipToInfo = $this->getRequest()->getPost('ship')) {
                $this->_getCheckout()->setShippingItemsInformation($shipToInfo);
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/addresses');
        }
        catch (Exception $e) {
            $this->_getCheckoutSession()->addException(
                $e,
                Mage::helper('Mage_Checkout_Helper_Data')->__('Data saving problem')
            );
            $this->_redirect('*/*/addresses');
        }
    }

    public function backToAddressesAction()
    {
        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES
        );
        $this->_getState()->unsCompleteStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
        );
        $this->_redirect('*/*/addresses');
    }

    /**
     * Multishipping checkout remove item action
     */
    public function removeItemAction()
    {
        $itemId     = $this->getRequest()->getParam('id');
        $addressId  = $this->getRequest()->getParam('address');
        if ($addressId && $itemId) {
            $this->_getCheckout()->setCollectRatesFlag(true);
            $this->_getCheckout()->removeAddressItem($addressId, $itemId);
        }
        $this->_redirect('*/*/addresses');
    }

    protected function _validateMinimumAmount()
    {
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $error = $this->_getCheckout()->getMinimumAmountError();
            $this->_getCheckout()->getCheckoutSession()->addError($error);
            $this->_forward('backToAddresses');
            return false;
        }
        return true;
    }

    /**
     * Multishipping checkout shipping information page
     */
    public function shippingAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SELECT_ADDRESSES)) {
            $this->_redirect('*/*/addresses');
            return $this;
        }

        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
        );
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Checkout_Model_Session');
        $this->renderLayout();
    }

    public function backToShippingAction()
    {
        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
        );
        $this->_getState()->unsCompleteStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
        );
        $this->_redirect('*/*/shipping');
    }

    public function shippingPostAction()
    {
        $shippingMethods = $this->getRequest()->getPost('shipping_method');
        try {
            Mage::dispatchEvent(
                'checkout_controller_multishipping_shipping_post',
                array('request'=>$this->getRequest(), 'quote'=>$this->_getCheckout()->getQuote())
            );
            $this->_getCheckout()->setShippingMethods($shippingMethods);
            $this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
            );
            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING
            );
            $this->_redirect('*/*/billing');
        }
        catch (Exception $e){
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/shipping');
        }
    }

    /**
     * Multishipping checkout billing information page
     */
    public function billingAction()
    {
        if (!$this->_validateBilling()) {
            return;
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_SHIPPING)) {
            $this->_redirect('*/*/shipping');
            return $this;
        }

        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
        );

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Checkout_Model_Session');
        $this->renderLayout();
    }

    /**
     * Validation of selecting of billing address
     *
     * @return boolean
     */
    protected function _validateBilling()
    {
        if(!$this->_getCheckout()->getQuote()->getBillingAddress()->getFirstname()) {
            $this->_redirect('*/multishipping_address/selectBilling');
            return false;
        }
        return true;
    }

    public function backToBillingAction()
    {
        $this->_getState()->setActiveStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
        );
        $this->_getState()->unsCompleteStep(
            Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW
        );
        $this->_redirect('*/*/billing');
    }

    /**
     * Multishipping checkout place order page
     */
    public function overviewAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return $this;
        }

        $this->_getState()->setActiveStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW);

        try {
            $payment = $this->getRequest()->getPost('payment', array());
            $payment['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_MULTISHIPPING
                | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
            $this->_getCheckout()->setPaymentMethod($payment);

            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_BILLING
            );

            $this->loadLayout();
            $this->_initLayoutMessages('Mage_Checkout_Model_Session');
            $this->_initLayoutMessages('Mage_Customer_Model_Session');
            $this->renderLayout();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_getCheckoutSession()->addException($e, $this->__('Cannot open the overview page'));
            $this->_redirect('*/*/billing');
        }
    }

    public function overviewPostAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        try {
            if ($requiredAgreements = Mage::helper('Mage_Checkout_Helper_Data')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $this->_getCheckoutSession()->addError($this->__('Please agree to all Terms and Conditions before placing the order.'));
                    $this->_redirect('*/*/billing');
                    return;
                }
            }

            $payment = $this->getRequest()->getPost('payment');
            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
            if (isset($payment['cc_number'])) {
                $paymentInstance->setCcNumber($payment['cc_number']);
            }
            if (isset($payment['cc_cid'])) {
                $paymentInstance->setCcCid($payment['cc_cid']);
            }
            $this->_getCheckout()->createOrders();
            $this->_getState()->setActiveStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_SUCCESS
            );
            $this->_getState()->setCompleteStep(
                Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW
            );
            $this->_getCheckout()->getCheckoutSession()->clear();
            $this->_getCheckout()->getCheckoutSession()->setDisplaySuccess(true);
            $this->_redirect('*/*/success');
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getCheckoutSession()->addError($message);
            }
            $this->_redirect('*/*/billing');
        } catch (Mage_Checkout_Exception $e) {
            Mage::helper('Mage_Checkout_Helper_Data')
                ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckout()->getCheckoutSession()->clear();
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/cart');
        }
        catch (Mage_Core_Exception $e){
            Mage::helper('Mage_Checkout_Helper_Data')
                ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (Exception $e){
            Mage::logException($e);
            Mage::helper('Mage_Checkout_Helper_Data')
                ->sendPaymentFailedEmail($this->_getCheckout()->getQuote(), $e->getMessage(), 'multi-shipping');
            $this->_getCheckoutSession()->addError($this->__('Order place error.'));
            $this->_redirect('*/*/billing');
        }
    }

    /**
     * Multishipping checkout succes page
     */
    public function successAction()
    {
        if (!$this->_getState()->getCompleteStep(Mage_Checkout_Model_Type_Multishipping_State::STEP_OVERVIEW)) {
            $this->_redirect('*/*/addresses');
            return $this;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Checkout_Model_Session');
        $ids = $this->_getCheckout()->getOrderIds();
        Mage::dispatchEvent('checkout_multishipping_controller_success_action', array('order_ids' => $ids));
        $this->renderLayout();
    }

    /**
     * Redirect to login page
     *
     */
    public function redirectLogin()
    {
        $this->setFlag('', 'no-dispatch', true);
        Mage::getSingleton('Mage_Customer_Model_Session')->setBeforeAuthUrl(Mage::getUrl('*/*', array('_secure'=>true)));

        $this->getResponse()->setRedirect(
            Mage::helper('Mage_Core_Helper_Url')->addRequestParam(
                $this->_getHelper()->getMSLoginUrl(),
                array('context' => 'checkout')
            )
        );

        $this->setFlag('', 'redirectLogin', true);
    }
}
