<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http\Client;

use Magento\Analytics\Model\Connector\Http\ResponseFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Psr\Log\LoggerInterface;

/**
 * A CURL HTTP client.
 *
 * Sends requests via a CURL adapter.
 */
class Curl implements \Magento\Analytics\Model\Connector\Http\ClientInterface
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
     * {@inheritdoc}
     */
    public function request($method, $url, $body = '', array $headers = [], $version = '1.1')
    {
        $curl = $this->curlFactory->create();

        $curl->write($method, $url, $version, $headers, $body);

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
