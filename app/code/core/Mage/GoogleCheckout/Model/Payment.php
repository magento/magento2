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
 * @package     Mage_GoogleCheckout
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_GoogleCheckout_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const ACTION_AUTHORIZE = 0;
    const ACTION_AUTHORIZE_CAPTURE = 1;

    protected $_code  = 'googlecheckout';
    protected $_formBlockType = 'Mage_GoogleCheckout_Block_Form';

    /**
     * Availability options
     */
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = false;
    protected $_canUseForMultishipping  = false;

    /**
     * Can be edit order (renew order)
     *
     * @return bool
     */
    public function canEdit()
    {
        return false;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('googlecheckout/redirect/redirect');
    }

    /**
     * Authorize
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_GoogleCheckout_Model_Payment
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $api = Mage::getModel('Mage_GoogleCheckout_Model_Api')->setStoreId($payment->getOrder()->getStoreId());
        $api->authorize($payment->getOrder()->getExtOrderId());

        return $this;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_GoogleCheckout_Model_Payment
     */
    public function capture(Varien_Object $payment, $amount)
    {
        /*
        try {
            $this->authorize($payment, $amount);
        } catch (Exception $e) {
            // authorization is not expired yet
        }
        */

        if ($payment->getOrder()->getPaymentAuthExpiration() < Mage::getModel('Mage_Core_Model_Date')->gmtTimestamp()) {
            try {
                $this->authorize($payment, $amount);
            } catch (Exception $e) {
                // authorization is not expired yet
            }
        }

        $api = Mage::getModel('Mage_GoogleCheckout_Model_Api')->setStoreId($payment->getOrder()->getStoreId());
        $api->charge($payment->getOrder()->getExtOrderId(), $amount);
        $payment->setForcedState(Mage_Sales_Model_Order_Invoice::STATE_OPEN);

        return $this;
    }

    /**
     * Refund money
     *
     * @param   Varien_Object $invoicePayment
     * @return  Mage_GoogleCheckout_Model_Payment
     */
    //public function refund(Varien_Object $payment, $amount)
    public function refund(Varien_Object $payment, $amount)
    {
        $hlp = Mage::helper('Mage_GoogleCheckout_Helper_Data');

//        foreach ($payment->getCreditMemo()->getCommentsCollection() as $comment) {
//            $this->setReason($hlp->__('See Comments'));
//            $this->setComment($comment->getComment());
//        }

        $reason = $this->getReason() ? $this->getReason() : $hlp->__('No Reason');
        $comment = $this->getComment() ? $this->getComment() : $hlp->__('No Comment');

        $api = Mage::getModel('Mage_GoogleCheckout_Model_Api')->setStoreId($payment->getOrder()->getStoreId());
        $api->refund($payment->getOrder()->getExtOrderId(), $amount, $reason, $comment);

        return $this;
    }

    public function void(Varien_Object $payment)
    {
        $this->cancel($payment);

        return $this;
    }

    /**
     * Void payment
     *
     * @param   Varien_Object $invoicePayment
     * @return  Mage_GoogleCheckout_Model_Payment
     */
    public function cancel(Varien_Object $payment)
    {
        if (!$payment->getOrder()->getBeingCanceledFromGoogleApi()) {
            $hlp = Mage::helper('Mage_GoogleCheckout_Helper_Data');
            $reason = $this->getReason() ? $this->getReason() : $hlp->__('Unknown Reason');
            $comment = $this->getComment() ? $this->getComment() : $hlp->__('No Comment');

            $api = Mage::getModel('Mage_GoogleCheckout_Model_Api')->setStoreId($payment->getOrder()->getStoreId());
            $api->cancel($payment->getOrder()->getExtOrderId(), $reason, $comment);
        }

        return $this;
    }

    /**
     * Retrieve information from payment configuration.
     * Rewrited because of custom node for checkout settings
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'google/checkout/' . $field;
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Check void availability
     *
     * @param   Varien_Object $payment
     * @return  bool
     */
    public function canVoid(Varien_Object $payment)
    {
        if ($payment instanceof Mage_Sales_Model_Order_Invoice
            || $payment instanceof Mage_Sales_Model_Order_Creditmemo
        ) {
            return false;
        }

        return $this->_canVoid;
    }
}
