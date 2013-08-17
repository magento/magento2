<?php
/**
 * Dispatches messages over HTTP
 *
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
 * @package     Magento_Outbound
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magento_Outbound_Transport_Http implements Magento_Outbound_TransportInterface
{
    /**
     * Http version used by Magento
     */
    const HTTP_VERSION = '1.1';

    /**
     * @var Varien_Http_Adapter_Curl
     */
    protected $_curl;

    /**
     * @param Varien_Http_Adapter_Curl $curl
     */
    public function __construct(Varien_Http_Adapter_Curl $curl)
    {
        $this->_curl = $curl;
    }

    /**
     * Dispatch message and return response
     *
     * @param Magento_Outbound_MessageInterface $message
     * @return Magento_Outbound_Transport_Http_Response
     */
    public function dispatch(Magento_Outbound_MessageInterface $message)
    {
        $config = array(
            'verifypeer' => TRUE,
            'verifyhost' => 2
        );

        $timeout = $message->getTimeout();
        if (!is_null($timeout) && $timeout > 0) {
            $config['timeout'] = $timeout;
        } else {
            $config['timeout'] = Magento_Outbound_Message::DEFAULT_TIMEOUT;
        }
        $this->_curl->setConfig($config);

        $this->_curl->write(Zend_Http_Client::POST,
            $message->getEndpointUrl(),
            self::HTTP_VERSION,
            $this->_prepareHeaders($message->getHeaders()),
            $message->getBody()
        );

        return new Magento_Outbound_Transport_Http_Response(Zend_Http_Response::fromString($this->_curl->read()));
    }

    /**
     * Prepare headers for dispatch
     *
     * @param string[] $headers
     * @return array
     */
    protected function _prepareHeaders($headers)
    {
        $result = array();
        foreach ($headers as $headerName => $headerValue) {
            $result[] = sprintf('%s: %s', $headerName, $headerValue);
        }
        return $result;
    }
}
