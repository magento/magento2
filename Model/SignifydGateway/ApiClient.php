<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

use Magento\Signifyd\Model\SignifydGateway\Client\RequestBuilder;

/**
 * Encapsulates Signifyd API protocol.
 * @since 2.2.0
 */
class ApiClient
{
    /**
     * @var RequestBuilder
     * @since 2.2.0
     */
    private $requestBuilder;

    /**
     * ApiClient constructor.
     *
     * @param RequestBuilder $requestBuilder
     * @since 2.2.0
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
     * @param array  $params
     * @return array
     * @since 2.2.0
     */
    public function makeApiCall($url, $method, array $params = [])
    {
        $result = $this->requestBuilder->doRequest($url, $method, $params);

        return $result;
    }
}
