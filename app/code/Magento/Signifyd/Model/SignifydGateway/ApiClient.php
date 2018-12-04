<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Signifyd\Model\SignifydGateway\Client\RequestBuilder;

/**
 * Encapsulates Signifyd API protocol.
 */
class ApiClient
{
    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * ApiClient constructor.
     *
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(
        RequestBuilder $requestBuilder
    ) {
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * Perform call to Signifyd API.
     *
     * Method returns associative array that corresponds to successful result.
     * Current implementation do not expose details in case of failure.
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param int|null $storeId
     * @return array
     * @throws ApiCallException
     * @throws \Zend_Http_Client_Exception
     */
    public function makeApiCall($url, $method, array $params = [], $storeId = null)
    {
        $result = $this->requestBuilder->doRequest($url, $method, $params, $storeId);

        return $result;
    }
}
