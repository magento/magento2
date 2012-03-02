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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales module base helper
 *
 * @category    Mage
 * @package     Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * Maximum available number
     */
    const MAXIMUM_AVAILABLE_NUMBER = 99999999;

    /**
     * Check quote amount
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param decimal $amount
     * @return Mage_Sales_Helper_Data
     */
    public function checkQuoteAmount(Mage_Sales_Model_Quote $quote, $amount)
    {
        if (!$quote->getHasError() && ($amount>=self::MAXIMUM_AVAILABLE_NUMBER)) {
            $quote->setHasError(true);
            $quote->addMessage(
                $this->__('Items maximum quantity or price do not allow checkout.')
            );
        }
        return $this;
    }

    /**
     * Check allow to send new order confirmation email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendNewOrderConfirmationEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order::XML_PATH_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send new order email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendNewOrderEmail($store = null)
    {
        return $this->canSendNewOrderConfirmationEmail($store);
    }

    /**
     * Check allow to send order comment email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendOrderCommentEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order::XML_PATH_UPDATE_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send new shipment email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendNewShipmentEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send shipment comment email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendShipmentCommentEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order_Shipment::XML_PATH_UPDATE_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send new invoice email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendNewInvoiceEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send invoice comment email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendInvoiceCommentEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order_Invoice::XML_PATH_UPDATE_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send new creditmemo email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendNewCreditmemoEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order_Creditmemo::XML_PATH_EMAIL_ENABLED, $store);
    }

    /**
     * Check allow to send creditmemo comment email
     *
     * @param mixed $store
     * @return bool
     */
    public function canSendCreditmemoCommentEmail($store = null)
    {
        return Mage::getStoreConfigFlag(Mage_Sales_Model_Order_Creditmemo::XML_PATH_UPDATE_EMAIL_ENABLED, $store);
    }
}
