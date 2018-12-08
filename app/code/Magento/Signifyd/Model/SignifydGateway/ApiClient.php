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
<<<<<<< HEAD
     * @throws ApiCallException
     * @throws \Zend_Http_Client_Exception
     */
    public function makeApiCall($url, $method, array $params = [], $storeId = null)
=======
     */
    public function makeApiCall($url, $method, array $params = [], $storeId = null): array
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $result = $this->requestBuilder->doRequest($url, $method, $params, $storeId);

        return $result;
    }
}
