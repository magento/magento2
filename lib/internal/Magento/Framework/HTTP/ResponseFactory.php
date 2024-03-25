<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP;

use Laminas\Http\Response;

/**
 * A factory for an HTTP response.
 */
class ResponseFactory
{
    /**
     * Creates a new Response object from a string.
     *
     * @param string $response
     * @return Response
     */
    public function create($response)
    {
        return Response::fromString($response);
    }
}
