<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

/**
 * Exception of interaction with Signifyd API
 * @since 2.2.0
 */
class ApiCallException extends GatewayException
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $requestData;

    /**
     * ApiCallException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @param string $requestData in JSON format
     * @since 2.2.0
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null, $requestData = '')
    {
        $this->requestData = $requestData;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets request data for unsuccessful request in JSON format
     * @return string
     * @since 2.2.0
     */
    public function getRequestData()
    {
        return $this->requestData;
    }
}
