<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Interface ResponseHandlerInterface
 */
interface ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     * @return bool|string
     */
    public function handleResponse(array $responseBody);
}
