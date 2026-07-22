<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents an interface for response handler which process response body.
 */
interface ResponseHandlerInterface
{
    /**
     * Process response body
     *
     * @param array $responseBody
     * @return bool|string
     */
    public function handleResponse(array $responseBody);
}
