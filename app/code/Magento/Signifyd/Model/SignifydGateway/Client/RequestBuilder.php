<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Client;

/**
 * Class RequestBuilder
 * Creates HTTP client, sends request to Signifyd and handles response
 */
class RequestBuilder
{
    /**
     * @var HttpClientFactory
     */
    private $clientCreator;

    /**
     * @var RequestSender
     */
    private $requestSender;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * RequestBuilder constructor.
     *
     * @param HttpClientFactory $clientCreator
     * @param RequestSender     $requestSender
     * @param ResponseHandler   $responseHandler
     */
    public function __construct(
        HttpClientFactory $clientCreator,
        RequestSender $requestSender,
        ResponseHandler $responseHandler
    ) {
        $this->clientCreator = $clientCreator;
        $this->requestSender = $requestSender;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Creates HTTP client for API call.
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param int|null $storeId
     * @return array
     */
    public function doRequest($url, $method, array $params = [], $storeId = null): array
    {
        $client = $this->clientCreator->create($url, $method, $params, $storeId);
        $response = $this->requestSender->send($client, $storeId);
        $result = $this->responseHandler->handle($response);

        return $result;
    }
}
