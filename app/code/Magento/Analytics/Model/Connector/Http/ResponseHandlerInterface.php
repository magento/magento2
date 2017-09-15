<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 * @since 2.2.0
 */
interface ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     * @return bool|string
     * @since 2.2.0
     */
    public function handleResponse(array $responseBody);
}
