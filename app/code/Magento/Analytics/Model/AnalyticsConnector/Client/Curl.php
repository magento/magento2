<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector\Client;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\Adapter\CurlFactory;

/**
 * A CURL HTTP client.
 *
 * Sends requests via a CURL adapter.
 */
class Curl
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param CurlFactory $curlFactory
     * @param ResponseFactory $responseFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurlFactory $curlFactory,
        ResponseFactory $responseFactory,
        LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * Sends a POST request using given parameters.
     *
     * Returns an HTTP response object or FALSE in case of failure.
     *
     * @param string $url
     * @param string $body
     * @param array $headers
     * @param string $version
     *
     * @return \Zend_Http_Response|bool
     */
    public function post($url, $body = '', array $headers = [], $version = '1.1')
    {
        $curl = $this->curlFactory->create();

        $curl->write(ZendClient::POST, $url, $version, $headers, $body);

        $result = $curl->read();

        if ($curl->getErrno()) {
            $this->logger->critical(
                new \Exception(
                    sprintf(
                        'MBI service CURL connection error #%s: %s',
                        $curl->getErrno(),
                        $curl->getError()
                    )
                )
            );

            return false;
        }

        return $this->responseFactory->create($result);
    }
}
