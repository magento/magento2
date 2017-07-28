<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\App\Request\Http;

/**
 *  Reads Signifyd webhook request data.
 * @since 2.2.0
 */
class WebhookRequest
{
    /**
     * @var Http
     * @since 2.2.0
     */
    private $request;

    /**
     * @param Http $request
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getHash()
    {
        return (string)$this->request->getHeader('X-SIGNIFYD-SEC-HMAC-SHA256');
    }

    /**
     * Returns event topic identifier.
     *
     * @return string
     * @since 2.2.0
     */
    public function getEventTopic()
    {
        return (string)$this->request->getHeader('X-SIGNIFYD-TOPIC');
    }

    /**
     * Returns raw data from the request body.
     *
     * @return string
     * @since 2.2.0
     */
    public function getBody()
    {
        return (string)$this->request->getContent();
    }
}
