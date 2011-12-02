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
 * PayPal Mobile Embedded Payments Checkout Module
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Payment_Method_Paypal_Mep extends Mage_Paypal_Model_Express
{
    /**
     * Store MEP payment method code
     */
    const MEP_METHOD_CODE = 'paypal_mep';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code  = self::MEP_METHOD_CODE;

    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_isInitializeNeeded      = false;
    protected $_canUseCheckout          = false;
    protected $_canManageRecurringProfiles = false;

    /**
     * Get config payment action url
     * Used to universalize payment actions when processing payment place
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $storeId = false;
        $model = Mage::registry('current_app');

        if (($model instanceof Mage_XmlConnect_Model_Application)) {
            $storeId = $model->getStoreId();
        }

        if (!$storeId) {
            $storeId = $quote ? $quote->getStoreId() : Mage::app()->getStore()->getId();
        }

        return (bool) Mage::getModel('Mage_Paypal_Model_Config')->setStoreId($storeId)
            ->isMethodAvailable(Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS);
    }

    /**
     * Capture payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $transactionId = $payment->getAdditionalInformation(
            Mage_XmlConnect_Model_Paypal_Mep_Checkout::PAYMENT_INFO_TRANSACTION_ID
        );
        $payment->setTransactionId($transactionId);
        return $this;
    }

    /**
     * Return title of the PayPal Mobile Embedded Payment method
     *
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('Mage_XmlConnect_Helper_Data')->__('PayPal MEP');
    }
}
