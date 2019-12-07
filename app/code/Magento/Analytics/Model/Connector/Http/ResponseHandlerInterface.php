<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 */
interface ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     * @return bool|string
     */
    public function handleResponse(array $responseBody);
}
