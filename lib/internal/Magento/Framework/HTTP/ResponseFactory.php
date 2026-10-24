<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
