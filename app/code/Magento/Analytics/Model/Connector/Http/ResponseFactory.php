<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * A factory for an HTTP response.
 * @since 2.2.0
 */
class ResponseFactory
{
    /**
     * Creates a new \Zend_Http_Response object from a string.
     *
     * @param string $response
     * @return \Zend_Http_Response
     * @since 2.2.0
     */
    public function create($response)
    {
        return \Zend_Http_Response::fromString($response);
    }
}
