<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Client;

use Magento\Signifyd\Model\SignifydGateway\Debugger\DebuggerFactory;
use Magento\Signifyd\Model\SignifydGateway\ApiCallException;
use Magento\Framework\HTTP\ZendClient;

/**
 * Class RequestSender
 * Gets HTTP client end sends request to Signifyd API
 */
class RequestSender
{
    /**
     * @var DebuggerFactory
     */
    private $debuggerFactory;

    /**
     * RequestSender constructor.
     *
     * @param DebuggerFactory $debuggerFactory
     */
    public function __construct(
        DebuggerFactory $debuggerFactory
    ) {
        $this->debuggerFactory = $debuggerFactory;
    }

    /**
     * Sends HTTP request to Signifyd API with configured client.
     *
     * Each request/response pair is handled by debugger.
     * If debug mode for Signifyd integration enabled in configuration
     * debug information is recorded to debug.log.
     *
     * @param ZendClient $client
     * @return \Zend_Http_Response
     * @throws ApiCallException
     */
    public function send(ZendClient $client)
    {
        try {
            $response = $client->request();

            $this->debuggerFactory->create()->success(
                $client->getUri(true),
                $client->getLastRequest(),
                $response->getStatus() . ' ' . $response->getMessage(),
                $response->getBody()
            );

            return $response;
        } catch (\Exception $e) {
            $this->debuggerFactory->create()->failure(
                $client->getUri(true),
                $client->getLastRequest(),
                $e
            );

            throw new ApiCallException(
                'Unable to process Signifyd API: ' . $e->getMessage(),
                $e->getCode(),
                $e,
                $client->getLastRequest()
            );
        }
    }
}
