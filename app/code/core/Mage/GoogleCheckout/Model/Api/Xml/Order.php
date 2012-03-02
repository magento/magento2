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

class Mage_GoogleCheckout_Model_Api_Xml_Order extends Mage_GoogleCheckout_Model_Api_Xml_Abstract
{
    protected function _getApiUrl()
    {
        $url = $this->_getBaseApiUrl();
        $url .= 'request/Merchant/'.Mage::getStoreConfig('google/checkout/merchant_id', $this->getStoreId());
        return $url;
    }

    protected function _processGResponse($response)
    {
        if ($response[0]===200) {
            return true;
        } else {
            $xml = simplexml_load_string(html_entity_decode($response[1]));
            if (!$xml || !$xml->{'error-message'}) {
                return false;
            }
            Mage::throwException($this->__('Google Checkout: %s', (string)$xml->{'error-message'}));
        }
    }

// FINANCIAL

    public function authorize()
    {
        $GRequest = $this->getGRequest();

        $postargs = '<?xml version="1.0" encoding="UTF-8"?>
            <authorize-order xmlns="'
            . $GRequest->schema_url
            . '" google-order-number="'
            . $this->getGoogleOrderNumber()
            . '"/>';

        $response = $GRequest->SendReq($GRequest->request_url,
                   $GRequest->GetAuthenticationHeaders(), $postargs);
        return $this->_processGResponse($response);
    }

    public function charge($amount)
    {
        $response = $this->getGRequest()
            ->SendChargeOrder($this->getGoogleOrderNumber(), $amount);
        return $this->_processGResponse($response);
    }

    public function refund($amount, $reason, $comment = '')
    {
        $response = $this->getGRequest()
            ->SendRefundOrder($this->getGoogleOrderNumber(), $amount, $reason, $comment);
        return $this->_processGResponse($response);
    }

    public function cancel($reason, $comment = '')
    {
        $response = $this->getGRequest()
            ->SendCancelOrder($this->getGoogleOrderNumber(), $reason, $comment);
        return $this->_processGResponse($response);
    }

// FULFILLMENT

    public function process()
    {
        $response = $this->getGRequest()
            ->SendProcessOrder($this->getGoogleOrderNumber());
        return $this->_processGResponse($response);
    }

    public function deliver($carrier, $trackingNo, $sendMail = true)
    {
        $response = $this->getGRequest()
            ->SendDeliverOrder($this->getGoogleOrderNumber(), $carrier, $trackingNo, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }

    public function addTrackingData($carrier, $trackingNo)
    {
        $response = $this->getGRequest()
            ->SendTrackingData($this->getGoogleOrderNumber(), $carrier, $trackingNo);
        return $this->_processGResponse($response);
    }

    public function shipItems($items, $sendMail = true)
    {
        $googleShipItems = array();
        foreach ($items as $item) {
            $googleShipItems[] = new GoogleShipItem($item);
        }

        $response = $this->getGRequest()
            ->SendShipItems($this->getGoogleOrderNumber(), $googleShipItems, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }

    public function backorderItems($items, $sendMail = true)
    {
        $response = $this->getGRequest()
            ->SendBackorderItems($this->getGoogleOrderNumber(), $items, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }

    public function cancelItems($items, $reason, $comment = '', $sendMail = true)
    {
        $response = $this->getGRequest()
            ->SendCancelItems($this->getGoogleOrderNumber(), $items, $reason, $comment, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }

    public function returnItems($items, $sendMail = true)
    {
        $response = $this->getGRequest()
            ->SendReturnItems($this->getGoogleOrderNumber(), $items, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }

    public function resetItems($items, $sendMail = true)
    {
        $response = $this->getGRequest()
            ->SendRResetItemsShippingInformation($this->getGoogleOrderNumber(), $items, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }

// MISC

    public function archive()
    {
        $response = $this->getGRequest()
            ->SendArchiveOrder($this->getGoogleOrderNumber());
        return $this->_processGResponse($response);
    }

    public function unarchive()
    {
        $response = $this->getGRequest()
            ->SendUnarchiveOrder($this->getGoogleOrderNumber());
        return $this->_processGResponse($response);
    }

    public function addOrderNumber($merchantOrder)
    {
        $response = $this->getGRequest()
            ->SendMerchantOrderNumber($this->getGoogleOrderNumber(), $merchantOrder);
        return $this->_processGResponse($response);
    }


    public function addBuyerMessage($message, $sendMail = true)
    {
        $response = $this->getGRequest()
            ->SendBuyerMessage($this->getGoogleOrderNumber(), $message, $sendMail ? 'true' : 'false');
        return $this->_processGResponse($response);
    }
}
