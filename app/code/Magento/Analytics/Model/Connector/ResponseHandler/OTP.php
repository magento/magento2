<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;

/**
 * Fetches OTP from body.
 * @since 2.2.0
 */
class OTP implements ResponseHandlerInterface
{
    /**
     * @param array $responseBody
     *
     * @return bool|string
     * @since 2.2.0
     */
    public function handleResponse(array $responseBody)
    {
        return !empty($responseBody['otp']) ? $responseBody['otp'] : false;
    }
}
