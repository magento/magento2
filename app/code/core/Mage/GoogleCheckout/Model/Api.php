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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_GoogleCheckout_Model_Api extends Varien_Object
{
    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    protected function _getApi($area)
    {
        $api = Mage::getModel('Mage_GoogleCheckout_Model_Api_Xml_' . uc_words($area))->setStoreId($this->getStoreId());
        $api->setApi($this);
        return $api;
    }

// CHECKOUT
    public function checkout(Mage_Sales_Model_Quote $quote)
    {
        $api = $this->_getApi('checkout')
            ->setQuote($quote)
            ->checkout();
        return $api;
    }

// FINANCIAL COMMANDS
    public function authorize($gOrderId)
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->authorize();
        return $api;
    }

    public function charge($gOrderId, $amount)
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->charge($amount);
        return $api;
    }

    public function refund($gOrderId, $amount, $reason, $comment = '')
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->refund($amount, $reason, $comment);
        return $api;
    }

    public function cancel($gOrderId, $reason, $comment = '')
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->cancel($reason, $comment);
        return $api;
    }

// FULFILLMENT COMMANDS (ORDER BASED)

    public function process($gOrderId)
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->process();
        return $api;
    }

    public function deliver($gOrderId, $carrier, $trackingNo, $sendMail = true)
    {
        $this->setCarriers(array('dhl' => 'DHL', 'fedex' => 'FedEx', 'ups' => 'UPS', 'usps' => 'USPS'));
        Mage::dispatchEvent('googlecheckout_api_deliver_carriers_array', array('api' => $this));
        $gCarriers = $this->getCarriers();
        $carrier = strtolower($carrier);
        $carrier = isset($gCarriers[$carrier]) ? $gCarriers[$carrier] : 'Other';

        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->deliver($carrier, $trackingNo, $sendMail);
        return $api;
    }

    public function addTrackingData($gOrderId, $carrier, $trackingNo)
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->addTrackingData($carrier, $trackingNo);
        return $api;
    }

// FULFILLMENT COMMANDS (ITEM BASED)

    public function shipItems($gOrderId, array $items)
    {
        $api = $this->_getApi('order')
            ->setGoogleOrderNumber($gOrderId)
            ->shipItems($items);
        return $api;
    }

    public function backorderItems()
    {
        $api = $this->_getApi('order')
            ->setOrder($order)
            ->setItems($items)
            ->shipItems();
        return $api;
    }

    public function returnItems()
    {
        $api = $this->_getApi('order')
            ->setOrder($order)
            ->setItems($items)
            ->shipItems();
        return $api;
    }

    public function cancelItems()
    {
        $api = $this->_getApi('order')
            ->setOrder($order)
            ->setItems($items)
            ->shipItems();
        return $api;
    }

    public function resetItemsShippingInformation()
    {

    }

    public function addMerchantOrderNumber()
    {

    }

    public function sendBuyerMessage()
    {
        $api = $this->_getApi('order')
            ->setOrder($order)
            ->setItems($items)
            ->shipItems();
        return $api;
    }

// OTHER ORDER COMMANDS

    public function archiveOrder()
    {
        $api = $this->_getApi('order')
            ->setOrder($order)
            ->setItems($items)
            ->shipItems();
        return $api;
    }

    public function unarchiveOrder()
    {
        $api = $this->_getApi('order')
            ->setOrder($order)
            ->setItems($items)
            ->shipItems();
        return $api;
    }

// WEB SERVICE SERVER PROCEDURES

    public function processCallback()
    {
        $api = $this->_getApi('callback')->process();
        return $api;
    }

    /**
     * Log debug data to file
     *
     * @param mixed $debugData
     */
    public function debugData($debugData)
    {
        if ($this->getDebugFlag()) {
            Mage::getModel('Mage_Core_Model_Log_Adapter', array('fileName' => 'payment_googlecheckout.log'))
               ->setFilterDataKeys($this->_debugReplacePrivateDataKeys)
               ->log($debugData);
        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        if (!$this->hasData('debug_flag')) {
            $this->setData('debug_flag', Mage::getStoreConfig('google/checkout/debug', $this->getStoreId()));
        }
        return $this->getData('debug_flag');
    }
}
