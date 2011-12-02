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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect checkout controller
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_CheckoutController extends Mage_XmlConnect_Controller_Action
{
    /**
     * Make sure customer is logged in
     *
     * @return null
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $quote = Mage::getSingleton('Mage_Checkout_Model_Session')->getQuote();
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
            && !Mage::helper('Mage_Checkout_Helper_Data')->isAllowedGuestCheckout($quote)
        ) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_message(
                $this->__('Customer not logged in.'),
                self::MESSAGE_STATUS_ERROR,
                array('logged_in' => '0')
            );
            return ;
        }
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Type_Onepage');
    }

    /**
     * Onepage Checkout page
     *
     * @return null
     */
    public function indexAction()
    {
        if (!Mage::helper('Mage_Checkout_Helper_Data')->canOnepageCheckout()) {
            $this->_message($this->__('Onepage checkout is disabled.'), self::MESSAGE_STATUS_ERROR);
            return;
        }
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $this->getOnepage()->getQuote();
        if ($quote->getHasError()) {
            $this->_message($this->__('Cart has some errors.'), self::MESSAGE_STATUS_ERROR);
            return;
        } else if (!$quote->hasItems()) {
            $this->_message($this->__('Cart is empty.'), self::MESSAGE_STATUS_ERROR);
            return;
        } else if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            $this->_message($error, self::MESSAGE_STATUS_ERROR);
            return;
        }
        Mage::getSingleton('Mage_Checkout_Model_Session')->setCartWasUpdated(false);
        $this->getOnepage()->initCheckout();

        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load checkout.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Display customer new billing addrress form
     *
     * @return null
     */
    public function newBillingAddressFormAction()
    {
        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load billing address form.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Display customer new shipping addrress form
     *
     * @return null
     */
    public function newShippingAddressFormAction()
    {
        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load shipping address form.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Billing addresses list action
     *
     * @return null
     */
    public function billingAddressAction()
    {
        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load billing address.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Save billing address to current quote using onepage model
     *
     * @return null
     */
    public function saveBillingAddressAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_message($this->__('Specified invalid data.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $data = $this->getRequest()->getPost('billing', array());
        $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);
        }
        $result = $this->getOnepage()->saveBilling($data, $customerAddressId);
        if (!isset($result['error'])) {
            $this->_message($this->__('Billing address has been set.'), self::MESSAGE_STATUS_SUCCESS);
        } else {
            if (!is_array($result['message'])) {
                $result['message'] = array($result['message']);
            }
            $this->_message(implode('. ', $result['message']), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Shipping addresses list action
     *
     * @return null
     */
    public function shippingAddressAction()
    {
        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load billing address.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Save shipping address to current quote using onepage model
     *
     * @return null
     */
    public function saveShippingAddressAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_message($this->__('Specified invalid data.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $data = $this->getRequest()->getPost('shipping', array());
        $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
        /**
         * For future use, please do not remove for now
         */
        $useForShipping = $this->getRequest()->getPost('use_for_shipping');

        $billingAddress = $this->getOnepage()->getQuote()->getBillingAddress();
        /**
         * Checking whether shipping address is the same with billing address?
         * This should be removed when mobile app will send just the 'use_for_shipping' flag
         */
        if (is_null($useForShipping)) {
            $useForShipping = $this->_checkUseForShipping($data, $billingAddress, $customerAddressId);
        }

        if ($useForShipping) {
            /**
             * Set address Id with the billing address Id
             */
            $customerAddressId = $billingAddress->getId();
            /**
             * Set flag of shipping address is same as billing address
             */
            $data['same_as_billing'] = true;
        }
        $result = $this->getOnepage()->saveShipping($data, $customerAddressId);
        if (!isset($result['error'])) {
            $this->_message($this->__('Shipping address has been set.'), self::MESSAGE_STATUS_SUCCESS);
        } else {
            if (!is_array($result['message'])) {
                $result['message'] = array($result['message']);
            }
            $this->_message(implode('. ', $result['message']), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Checks the shipping address is equal with billing address
     *
     * ATTENTION!!!
     * It should be removed when mobile app will send just the 'use_for_shipping' flag
     * instead of send shipping address same as a billing address
     *
     * @todo Remove when mobile app will send just the 'use_for_shipping' flag
     * @param array $data
     * @param Mage_Sales_Model_Quote_Address $billingAddress
     * @param integer $shippingAddressId
     * @return bool
     */
    protected function _checkUseForShipping(array $data, $billingAddress, $shippingAddressId)
    {
        $useForShipping = !$shippingAddressId || $billingAddress->getId() == $shippingAddressId;

        if ($useForShipping) {
            foreach ($data as $key => $value) {
                if ($key == 'save_in_address_book') {
                    continue;
                }
                $billingData = $billingAddress->getDataUsingMethod($key);
                if (is_array($value) && is_array($billingData)) {
                    foreach ($value as $k => $v) {
                        if (!isset($billingData[$k]) || $billingData[$k] != trim($v)) {
                            $useForShipping = false;
                            break;
                        }
                    }
                } else {
                    if (is_string($value) && $billingData != trim($value)) {
                        $useForShipping = false;
                        break;
                    } else {
                        $useForShipping = false;
                        break;
                    }
                }
            }
        }
        return $useForShipping;
    }

    /**
     * Get shipping methods for current quote
     *
     * @return null
     */
    public function shippingMethodsAction()
    {
        try {
            $result = array('error' => $this->__('Error.'));
            $this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->loadLayout(false);
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->_message($result['error'], self::MESSAGE_STATUS_ERROR);
    }

    /**
     * Shipping method save action
     *
     * @return null
     */
    public function saveShippingMethodAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_message($this->__('Specified invalid data.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $data = $this->getRequest()->getPost('shipping_method', '');
        $result = $this->getOnepage()->saveShippingMethod($data);
        if (!$result) {

            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array(
                'request' => $this->getRequest(),
                'quote' => $this->getOnepage()->getQuote()
            ));
            $this->getOnepage()->getQuote()->collectTotals()->save();

            $this->_message($this->__('Shipping method has been set.'), self::MESSAGE_STATUS_SUCCESS);
        } elseif(isset($result['error'])) {
            if (!is_array($result['message'])) {
                $result['message'] = array($result['message']);
            }
            Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array(
                'request' => $this->getRequest(),
                'quote' => $this->getOnepage()->getQuote()
            ));
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->_message(implode('. ', $result['message']), self::MESSAGE_STATUS_ERROR);
        }
    }


    /**
     * Save checkout method
     *
     * @return null
     */
    public function saveMethodAction()
    {
        if ($this->getRequest()->isPost()) {
            $method = (string) $this->getRequest()->getPost('method');
            $result = $this->getOnepage()->saveCheckoutMethod($method);
            if (!isset($result['error'])) {
                $this->_message($this->__('Payment Method has been set.'), self::MESSAGE_STATUS_SUCCESS);
            } else {
                if (!is_array($result['message'])) {
                    $result['message'] = array($result['message']);
                }
                $this->_message(implode('. ', $result['message']), self::MESSAGE_STATUS_ERROR);
            }
        }
    }

    /**
     * Get payment methods action
     *
     * @return null
     */
    public function paymentMethodsAction()
    {
        try {
            $this->loadLayout(false);
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $this->_message($result['error'], self::MESSAGE_STATUS_ERROR);
    }

    /**
     * Save payment action
     *
     * @return null
     */
    public function savePaymentAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_message($this->__('Specified invalid data.'), self::MESSAGE_STATUS_ERROR);
            return;
        }
        try {
            // set payment to quote
            $result = array();
            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);
            $this->_message($this->__('Payment method was successfully set.'), self::MESSAGE_STATUS_SUCCESS);
            return;
        } catch (Mage_Payment_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $e->getMessage();
        }
        $this->_message($result['error'], self::MESSAGE_STATUS_ERROR);
    }

    /**
     * Order summary info action
     *
     * @return null
     */
    public function orderReviewAction()
    {
        $this->getOnepage()->getQuote()->collectTotals()->save();
        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load order review.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Create order action
     *
     * @return null
     */
    public function saveOrderAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_message($this->__('Specified invalid data.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $checkoutHelper = Mage::helper('Mage_Checkout_Helper_Data');
        try {
            if ($requiredAgreements = $checkoutHelper->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if (array_diff($requiredAgreements, $postedAgreements)) {
                    $error = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->_message($error, self::MESSAGE_STATUS_ERROR);
                    return;
                }
            }
            if ($data = $this->getRequest()->getPost('payment', false)) {
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }
            $this->getOnepage()->saveOrder();

            /** @var $message Mage_XmlConnect_Model_Simplexml_Element */
            $message = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<message></message>');
            $message->addChild('status', self::MESSAGE_STATUS_SUCCESS);

            $orderId = $this->getOnepage()->getLastOrderId();

            $text = $this->__('Thank you for your purchase! ');
            $text .= $this->__('Your order # is: %s. ', $orderId);
            $text .= $this->__('You will receive an order confirmation email with details of your order and a link to track its progress.');
            $message->addChild('text', $text);

            $message->addChild('order_id', $orderId);

            $this->getOnepage()->getQuote()->save();
            $this->getOnepage()->getCheckout()->clear();

            $this->getResponse()->setBody($message->asNiceXml());
            return;
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            $checkoutHelper->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $error = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $checkoutHelper->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $error = $this->__('An error occurred while processing your order. Please contact us or try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        $this->_message($error, self::MESSAGE_STATUS_ERROR);
    }
}
