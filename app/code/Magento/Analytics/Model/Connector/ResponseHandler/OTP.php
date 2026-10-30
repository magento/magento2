<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\Http\ResponseHandlerInterface;

/**
 * Fetches OTP from body.
 */
class OTP implements ResponseHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handleResponse(array $responseBody)
    {
        return !empty($responseBody['otp']) ? $responseBody['otp'] : false;
    }
}
