<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 *
 * @deprecated 103.0.2
 * @see \Psr\Http\Client\ClientInterface
 */
interface ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     * @return bool|string
     */
    public function handleResponse(array $responseBody);
}
