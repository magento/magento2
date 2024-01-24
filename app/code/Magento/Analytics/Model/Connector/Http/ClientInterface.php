<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

use Laminas\Http\Response;

/**
 * An interface for an HTTP client.
 *
 * Send requests via a proper adapter.
 */
interface ClientInterface
{
    /**
     * Sends a request using given parameters.
     *
     * Returns an HTTP response object or FALSE in case of failure.
     *
     * @param string $method
     * @param string $url
     * @param array $body
     * @param array $headers
     * @param string $version
     *
     * @return Response
     */
    public function request($method, $url, array $body = [], array $headers = [], $version = '1.1');
}
