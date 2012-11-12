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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect checkout controller
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Paypal_MepController extends Mage_XmlConnect_Controller_Action
{
    /**
     * Store MEP checkout model instance
     *
     * @var Mage_XmlConnect_Model_Paypal_Mep_Checkout
     */
    protected $_checkout = null;

    /**
     * Store Quote mdoel instance
     *
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = false;

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
                $this->__('Customer not logged in.'), self::MESSAGE_STATUS_ERROR, array('logged_in' => '0')
            );
            return;
        }
    }

    /**
     * Start MEP Checkout
     *
     * @return null
     */
    public function indexAction()
    {
        try {
            if (is_object(Mage::getConfig()->getNode('modules/Enterprise_GiftCardAccount'))) {
                $giftcardInfoBlock = $this->getLayout()->addBlock(
                    'Enterprise_GiftCardAccount_Block_Checkout_Onepage_Payment_Additional', 'giftcard_info'
                );

                if (intval($giftcardInfoBlock->getAppliedGiftCardAmount())) {
                    $this->_message(
                        $this->__('Paypal MEP doesn\'t support checkout with any discount.'),
                        self::MESSAGE_STATUS_ERROR
                    );
                    return;
                }
            }

            $this->_initCheckout();
            $this->_checkout->initCheckout();
            $this->_message($this->__('Checkout has been initialized.'), self::MESSAGE_STATUS_SUCCESS);
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to start MEP Checkout.'), self::MESSAGE_STATUS_ERROR);
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

        try {
            $this->_initCheckout();
            $data = $this->getRequest()->getPost('shipping', array());

            array_walk_recursive($data, create_function('&$val', '$val = trim($val);'));

            if (!empty($data['region']) && isset($data['country_id'])) {
                $region = Mage::getModel('Mage_Directory_Model_Region')->loadByCode($data['region'], $data['country_id']);
                if ($region && $region->getId()) {
                    $data['region_id'] = $region->getId();
                }
            }

            $result = $this->_checkout->saveShipping($data);
            if (!isset($result['error'])) {
                $this->_message(
                    $this->__('Shipping address has been set.'),
                    self::MESSAGE_STATUS_SUCCESS
                );
            } else {
                if (!is_array($result['message'])) {
                    $result['message'] = array($result['message']);
                }
                $this->_message(implode('. ', $result['message']), self::MESSAGE_STATUS_ERROR);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to save shipping address.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Get shipping methods for current quote
     *
     * @return null
     */
    public function shippingMethodsAction()
    {
        try {
            $this->_initCheckout();
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to get shipping methods list.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
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

        try {
            $this->_initCheckout();
            $data = $this->getRequest()->getPost('shipping_method', '');
            $this->_getQuote()->getShippingAddress()->setShippingMethod($data)->setCollectShippingRates(true)->save();
            $result = $this->_checkout->saveShippingMethod($data);

            if (!isset($result['error'])) {
                /** @var $message Mage_XmlConnect_Model_Simplexml_Element */
                $message = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
                    array('data' => '<message></message>'));
                $message->addChild('status', self::MESSAGE_STATUS_SUCCESS);
                $message->addChild('text', $this->__('Shipping method has been set.'));
                if ($this->_getQuote()->isVirtual()) {
                    $quoteAddress = $this->_getQuote()->getBillingAddress();
                } else {
                    $quoteAddress = $this->_getQuote()->getShippingAddress();
                }
                $taxAmount = Mage::helper('Mage_Core_Helper_Data')->currency($quoteAddress->getBaseTaxAmount(), false, false);
                $message->addChild('tax_amount', Mage::helper('Mage_XmlConnect_Helper_Data')->formatPriceForXml($taxAmount));
                $this->_getQuote()->collectTotals()->save();
                $this->getResponse()->setBody($message->asNiceXml());
            } else {
                if (!is_array($result['message'])) {
                    $result['message'] = array($result['message']);
                }
                $this->_message(implode('. ', $result['message']), self::MESSAGE_STATUS_ERROR);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to save shipping method.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Shopping cart totals
     *
     * @return null
     */
    public function cartTotalsAction()
    {
        try {
            $this->_initCheckout();
            $this->loadLayout(false);
            $this->renderLayout();
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to collect cart totals.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Submit the order
     *
     * @return null
     */
    public function saveOrderAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->_message($this->__('Specified invalid data.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        try {
            /**
             * Init checkout
             */
            $this->_initCheckout();

            /**
             * Set payment data
             */
            $data = $this->getRequest()->getPost('payment', array());

            if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $data['payer'] = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getEmail();
            }

            $this->_checkout->savePayment($data);

            /**
             * Place order
             */
            $this->_checkout->saveOrder();

            /**
             * Format success report
             */
            /** @var $message Mage_XmlConnect_Model_Simplexml_Element */
            $message = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
                array('data' => '<message></message>'));
            $message->addChild('status', self::MESSAGE_STATUS_SUCCESS);

            $orderId = $this->_checkout->getLastOrderId();

            $text = $this->__('Thank you for your purchase! ');
            $text .= $this->__('Your order # is: %s. ', $orderId);
            $text .= $this->__('You will receive an order confirmation email with details of your order and a link to track its progress.');
            $message->addChild('text', $text);

            $message->addChild('order_id', $orderId);
            $this->getResponse()->setBody($message->asNiceXml());
            return;
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to place the order.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Instantiate quote and checkout
     *
     * @throws Mage_Core_Exception
     * @return null
     */
    protected function _initCheckout()
    {

        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            Mage::throwException($this->__('Unable to initialize MEP Checkout.'));
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::throwException($error);
        }
        $this->_getCheckoutSession()->setCartWasUpdated(false);

        $this->_checkout = Mage::getSingleton('Mage_XmlConnect_Model_Paypal_Mep_Checkout',
            array('params' =>
                array('quote'  => $quote)
        ));
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('Mage_Checkout_Model_Session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }
}
