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
 * @package     Magento_GoogleCheckout
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GoogleCheckout\Model;

class Api extends \Magento\Object
{
    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Stdlib\String
     */
    protected $string;

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Stdlib\String $string
     * @param array $data
     */
    public function __construct(
        \Magento\ObjectManager $objectManager,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Stdlib\String $string,
        array $data = array()
    ) {
        $this->objectManager = $objectManager;
        $this->_eventManager = $eventManager;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->string = $string;
        parent::__construct($data);
    }

    protected function _getApi($area)
    {
        $api = $this->objectManager->create(
            'Magento\GoogleCheckout\Model\Api\Xml\\' . $this->string->upperCaseWords($area)
        )->setStoreId($this->getStoreId());
        $api->setApi($this);
        return $api;
    }

// CHECKOUT
    public function checkout(\Magento\Sales\Model\Quote $quote)
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
        $this->_eventManager->dispatch('googlecheckout_api_deliver_carriers_array', array('api' => $this));
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
            $this->objectManager->create(
                'Magento\Logger\Adapter',
                array('fileName' => 'payment_googlecheckout.log')
            )->setFilterDataKeys($this->_debugReplacePrivateDataKeys)->log($debugData);
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
            $this->setData('debug_flag', $this->_coreStoreConfig->getConfig('google/checkout/debug', $this->getStoreId()));
        }
        return $this->getData('debug_flag');
    }
}
