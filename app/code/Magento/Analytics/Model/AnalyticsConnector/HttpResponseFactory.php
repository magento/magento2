<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector;

/**
 * A factory for an HTTP response.
 */
class HttpResponseFactory
{
    /**
     * Creates a new \Zend_Http_Response object from a string.
     *
     * @param string $response
     * @return \Zend_Http_Response
     */
    public function create($response)
    {
        return \Zend_Http_Response::fromString($response);
    }
}
