<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

/**
 * Use this interface to implement a processor for each entry of a header in an HTTP GraphQL request.
 */
interface HttpHeaderProcessorInterface
{
    /**
     * Perform processing on a list of headers, iteratively.
     *
     * This method should be called even if a header entry is not present on a request
     * to enforce required headers like "application/json"
     *
     * @param string $headerValue
     * @return void
     */
    public function processHeaderValue(string $headerValue) : void;
}
