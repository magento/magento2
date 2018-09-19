<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use Magento\Framework\App\Request\Http;

/**
 * Hold information about all http processors objects that can handle a header entry
 */
class HttpRequestProcessor
{
    /**
     * @var HttpHeaderProcessorInterface[]
     */
    private $headerProcessors = [];

    /**
     * @param HttpHeaderProcessorInterface[] $graphQlHeaders
     */
    public function __construct(array $graphQlHeaders = [])
    {
        $this->headerProcessors = $graphQlHeaders;
    }

    /**
     * Process the headers from a request given from usually the controller
     *
     * @param Http $request
     * @return void
     */
    public function processHeaders(Http $request) : void
    {
        foreach ($this->headerProcessors as $headerName => $headerClass) {
            $headerClass->processHeaderValue((string)$request->getHeader($headerName));
        }
    }
}
