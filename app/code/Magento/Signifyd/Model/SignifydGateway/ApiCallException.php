<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

/**
 * Exception of interaction with Signifyd API
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class ApiCallException extends GatewayException
{
    /**
     * @var string
     */
    private $requestData;

    /**
     * ApiCallException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param string $requestData in JSON format
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null, $requestData = '')
    {
        $this->requestData = $requestData;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets request data for unsuccessful request in JSON format
     * @return string
     */
    public function getRequestData()
    {
        return $this->requestData;
    }
}
