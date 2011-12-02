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
 * @package     Mage_PaypalUk
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Website Payments Pro (Payflow Edition) implementation for payment method instances
 * This model was created because right now PayPal Direct and PayPal Express payment
 * (Payflow Edition) methods cannot have same abstract
 */
class Mage_PaypalUk_Model_Pro extends Mage_Paypal_Model_Pro
{
    /**
     * Api model type
     *
     * @var string
     */
    protected $_apiType = 'paypaluk/api_nvp';

    /**
     * Config model type
     *
     * @var string
     */
    protected $_configType = 'Mage_Paypal_Model_Config';

    /**
     * Payflow trx_id key in transaction info
     *
     * @var string
     */
    const TRANSPORT_PAYFLOW_TXN_ID = 'payflow_trxid';

    /**
     * Refund a capture transaction
     *
     * @param Varien_Object $payment
     * @param float $amount
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if ($captureTxnId = $this->_getParentTransactionId($payment)) {
            $api = $this->getApi();
            $api->setAuthorizationId($captureTxnId);
        }
        parent::refund($payment, $amount);
    }

    /**
     * Is capture request needed on this transaction
     *
     * @return true
     */
    protected function _isCaptureNeeded()
    {
        return true;
    }

    /**
     * Get payflow transaction id from parent transaction
     *
     * @param Varien_Object $payment
     * @return string
     */
    protected function _getParentTransactionId(Varien_Object $payment)
    {
        if ($payment->getParentTransactionId()) {
            return $payment->getTransaction($payment->getParentTransactionId())
                ->getAdditionalInformation(Mage_PaypalUk_Model_Pro::TRANSPORT_PAYFLOW_TXN_ID);
        }
        return $payment->getParentTransactionId();
    }

    /**
     * Import capture results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     */
    protected function _importCaptureResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getPaypalTransactionId())
            ->setIsTransactionClosed(false)
            ->setTransactionAdditionalInfo(
                Mage_PaypalUk_Model_Pro::TRANSPORT_PAYFLOW_TXN_ID,
                $api->getTransactionId()
        );
        $payment->setPreparedMessage(
            Mage::helper('Mage_PaypalUk_Helper_Data')->__('Payflow PNREF: #%s.', $api->getTransactionId())
        );
        Mage::getModel('Mage_Paypal_Model_Info')->importToPayment($api, $payment);
    }

    /**
     * Fetch transaction details info method does not exists in PaypalUK
     *
     * @param Mage_Payment_Model_Info $payment
     * @param string $transactionId
     * @throws Mage_Core_Exception
     * @return void
     */
    public function fetchTransactionInfo(Mage_Payment_Model_Info $payment, $transactionId)
    {
        Mage::throwException(
            Mage::helper('Mage_PaypalUk_Helper_Data')->__('Fetch transaction details method does not exists in PaypalUK')
        );
    }

    /**
     * Import refund results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     * @param bool $canRefundMore
     */
    protected function _importRefundResultToPayment($api, $payment, $canRefundMore)
    {
        $payment->setTransactionId($api->getPaypalTransactionId())
            ->setIsTransactionClosed(1) // refund initiated by merchant
            ->setShouldCloseParentTransaction(!$canRefundMore)
            ->setTransactionAdditionalInfo(
                Mage_PaypalUk_Model_Pro::TRANSPORT_PAYFLOW_TXN_ID,
                $api->getTransactionId()
        );
        $payment->setPreparedMessage(
            Mage::helper('Mage_PaypalUk_Helper_Data')->__('Payflow PNREF: #%s.', $api->getTransactionId())
        );
        Mage::getModel('Mage_Paypal_Model_Info')->importToPayment($api, $payment);
    }
}
