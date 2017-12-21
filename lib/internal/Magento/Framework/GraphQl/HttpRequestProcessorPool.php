<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl;

use Magento\Framework\App\Request\Http;

/**
 * Hold information about all http processors objects that can handle a header entry
 */
class HttpRequestProcessorPool
{
    /**
     * @var HttpHeaderProcessorInterface[]
     */
    private $graphQlHeaders = [];

    /**
     * @param HttpHeaderProcessorInterface[] $graphQlHeaders
     */
    public function __construct(array $graphQlHeaders)
    {
        $this->graphQlHeaders = $graphQlHeaders;
    }

    /**
     * Process the headers from a request given from usually the controller
     *
     * @param Http $request
     */
    public function processHeaders(Http $request)
    {
        foreach ($this->graphQlHeaders as $headerName => $headerClass) {
            $headerClass->processHeaderValue($request->getHeader($headerName));
        }
    }
}
