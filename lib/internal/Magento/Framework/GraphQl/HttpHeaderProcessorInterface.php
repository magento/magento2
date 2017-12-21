<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\GraphQl;

/**
 * Use this interface to implement a handler for each entry of a header in an HTTP GraphQL request.
 */
interface HttpHeaderProcessorInterface
{
    /**
     * Process a header value individually from a list of headers and handle specific logic.
     *
     * This method should be called even if a header entry is not present on a request
     * to enforce required headers like "application/json"
     *
     * @param bool|string $headerValue
     * @return void
     */
    public function processHeaderValue($headerValue);
}
