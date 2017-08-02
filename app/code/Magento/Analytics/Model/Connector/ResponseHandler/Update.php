<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;

/**
 * Return positive answer that request was finished successfully.
 * @since 2.2.0
 */
class Update implements ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     *
     * @return bool|string
     * @since 2.2.0
     */
    public function handleResponse(array $responseBody)
    {
        return true;
    }
}
