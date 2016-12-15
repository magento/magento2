<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway;

/**
 * Exception of interaction with Signifyd API
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
