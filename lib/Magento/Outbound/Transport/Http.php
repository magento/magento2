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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Outbound\Transport;

use Magento\HTTP\Adapter\Curl;
use Magento\Outbound\Message;
use Magento\Outbound\MessageInterface;
use Magento\Outbound\TransportInterface;
use Magento\Outbound\Transport\Http\Response;

class Http implements TransportInterface
{
    /**
     * Http version used by Magento
     */
    const HTTP_VERSION = '1.1';

    /**
     * @var Curl
     */
    protected $_curl;

    /**
     * @param Curl $curl
     */
    public function __construct(Curl $curl)
    {
        $this->_curl = $curl;
    }

    /**
     * Dispatch message and return response
     *
     * @param MessageInterface $message
     * @return Response
     */
    public function dispatch(MessageInterface $message)
    {
        $config = array(
            'verifypeer' => TRUE,
            'verifyhost' => 2
        );

        $timeout = $message->getTimeout();
        if (!is_null($timeout) && $timeout > 0) {
            $config['timeout'] = $timeout;
        } else {
            $config['timeout'] = Message::DEFAULT_TIMEOUT;
        }
        $this->_curl->setConfig($config);

        $this->_curl->write(\Zend_Http_Client::POST,
            $message->getEndpointUrl(),
            self::HTTP_VERSION,
            $this->_prepareHeaders($message->getHeaders()),
            $message->getBody()
        );

        return new Response($this->_curl->read());
    }

    /**
     * Prepare headers for dispatch
     *
     * @param string[] $headers
     * @return string[]
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
