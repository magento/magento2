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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Transport_Http implements Mage_Webhook_Model_Transport_Interface
{
    const HTTP_VERSION = '1.1';

    public function dispatchMessage(Mage_Webhook_Model_Message $message, Mage_Webhook_Model_Subscriber $subscription)
    {
        if (!$message) {
            throw Mage::exception('Mage_Webhook', 'Trying to send message when none exists.');
        }

        if (!$subscription) {
            throw Mage::exception('Mage_Webhook', 'Trying to send message to a non-existent subscription.');
        }

        $request = $this->_generateHttpRequest($message, $subscription);

        $response = $this->_sendRequest($request, $subscription);

        return $response;
    }

    protected function _generateHttpRequest(Mage_Webhook_Model_Message $message, Mage_Webhook_Model_Subscriber $subscription)
    {
        $request = Mage::getModel('Mage_Webhook_Model_Transport_Http_Request')
            ->setMethod(Zend_Http_Client::POST)
            ->setUrl($subscription->getEndpointUrl())
            ->setHeaders($message->getHeaders())
            ->setBody($message->getBody());

        $request = $subscription->getAuthenticationModel()->signRequest($request, $subscription);

        return $request;
    }

    protected function _sendRequest(Mage_Webhook_Model_Transport_Http_Request $request,
                                    Mage_Webhook_Model_Subscriber $subscription)
    {
        $adapter = $this->_getAdapter();

        $this->_setAdapterConfig($adapter, $subscription);

        $adapter->write($request->getMethod(),
            $request->getUrl(),
            self::HTTP_VERSION,
            $this->_prepareHeaders($request->getHeaders()),
            $request->getBody()
        );
        $result = $adapter->read();

        $response = Zend_Http_Response::fromString($result);

        return new Mage_Webhook_Model_Transport_Http_Response($response);
    }

    protected function _setAdapterConfig(Varien_Http_Adapter_Curl $adapter,
                                         Mage_Webhook_Model_Subscriber $subscription)
    {
        $config = array(
            'verifypeer' => TRUE,
            'verifyhost' => 2 // Important to be 2
        );

        $timeout = $subscription->getTimeoutInSecs();
        if (!is_null($timeout) && $timeout > 0) {
            $config['timeout'] = $timeout;
        }

        $adapter->setConfig($config);
    }

    /**
     * @return Varien_Http_Adapter_Curl
     */
    protected function _getAdapter()
    {
        return new Varien_Http_Adapter_Curl();
    }

    protected function _prepareHeaders($headers)
    {
        $result = array();
        foreach ($headers as $headerName => $headerValue) {
            $result[] = sprintf('%s: %s', $headerName, $headerValue);
        }
        return $result;
    }
}
