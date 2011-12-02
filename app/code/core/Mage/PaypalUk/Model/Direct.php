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
 * PayPalUk Direct Module
 */
class Mage_PaypalUk_Model_Direct extends Mage_Paypal_Model_Direct
{
    protected $_code  = Mage_Paypal_Model_Config::METHOD_WPP_PE_DIRECT;

    /**
     * Website Payments Pro instance type
     *
     * @var string
     */
    protected $_proType = 'Mage_PaypalUk_Model_Pro';

    /**
     * Return available CC types for gateway based on merchant country
     *
     * @return string
     */
    public function getAllowedCcTypes()
    {
        return $this->_pro->getConfig()->cctypes;
    }

    /**
     * Merchant country limitation for 3d secure feature, rewrite for parent implementation
     *
     * @return bool
     */
    public function getIsCentinelValidationEnabled()
    {
        if (!parent::getIsCentinelValidationEnabled()) {
            return false;
        }
        // available only for US and UK merchants
        if (in_array($this->_pro->getConfig()->getMerchantCountry(), array('US', 'GB'))) {
            return true;
        }
        return false;
    }

    /**
     * Import direct payment results to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     */
    protected function _importResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getPaypalTransactionId())->setIsTransactionClosed(0)
            ->setIsTransactionPending($api->getIsPaymentPending())
            ->setTransactionAdditionalInfo(Mage_PaypalUk_Model_Pro::TRANSPORT_PAYFLOW_TXN_ID, $api->getTransactionId())
            ;
        $payment->setPreparedMessage(Mage::helper('Mage_PaypalUk_Helper_Data')->__('Payflow PNREF: #%s.', $api->getTransactionId()));
        $this->_pro->importPaymentInfo($api, $payment);
    }

    /**
     * Format credit card expiration date based on month and year values
     * Format: mmyy
     *
     * @param string|int $month
     * @param string|int $year
     * @return string
     */
    protected function _getFormattedCcExpirationDate($month, $year)
    {
        return sprintf('%02d', $month) . sprintf('%02d', substr($year, -2, 2));
    }
}
