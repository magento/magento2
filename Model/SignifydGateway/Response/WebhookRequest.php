<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\App\Request\Http;

/**
 *  Reads Signifyd webhook request data.
 */
class WebhookRequest
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Returns Base64 encoded output of the HMAC SHA256 encoding of the JSON body of the message.
     *
     * @return string
     */
    public function getHash()
    {
        return (string)$this->request->getHeader('X-SIGNIFYD-SEC-HMAC-SHA256');
    }

    /**
     * Returns event topic identifier.
     *
     * @return string
     */
    public function getEventTopic()
    {
        return (string)$this->request->getHeader('X-SIGNIFYD-TOPIC');
    }

    /**
     * Returns raw data from the request body.
     *
     * @return string
     */
    public function getBody()
    {
        return (string)@file_get_contents("php://input");
    }
}
