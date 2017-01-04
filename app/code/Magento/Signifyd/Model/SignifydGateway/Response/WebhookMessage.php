<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\DataObject;

/**
 * Webhooks are messages sent by SIGNIFYD via HTTP POST to a url you configure on your
 * Notifications page in the SIGNIFYD settings.
 *
 * WebhookMessage messages are sent when certain events occur in the life of an investigation.
 * They allow your application to receive pushed updates about a case, rather than poll SIGNIFYD for status changes.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/webhooks
 */
class WebhookMessage
{
    /**
     * Decoded webhook request body.
     *
     * @var array
     */
    private $data;

    /**
     * Event topic identifier.
     *
     * @var string
     */
    private $eventTopic;

    /**
     * Raw webhook request body.
     *
     * @var string
     */
    private $rawData;

    /**
     * Base64 encoded output of the HMAC SHA256 encoding of the JSON body of the message.
     *
     * @var string
     */
    private $expectedHash;

    /**
     * @param string $rawData
     * @param array $data
     * @param string $eventTopic
     * @param string $expectedHash
     */
    public function __construct(
        $rawData,
        array $data,
        $eventTopic,
        $expectedHash
    ) {
        $this->rawData = $rawData;
        $this->data = $data;
        $this->eventTopic = $eventTopic;
        $this->expectedHash = $expectedHash;
    }

    /**
     * Returns decoded webhook request body.
     *
     * @return DataObject
     */
    public function getData()
    {
        return new DataObject($this->data);
    }

    /**
     * Returns event topic identifier.
     *
     * @return string
     */
    public function getEventTopic()
    {
        return $this->eventTopic;
    }

    /**
     * Returns expected hash.
     *
     * @return string
     */
    public function getExpectedHash()
    {
        return $this->expectedHash;
    }

    /**
     * Returns actual hash based on raw request body and api key
     *
     * @param string $apiKey
     * @return string
     */
    public function getActualHash($apiKey)
    {
        return base64_encode(hash_hmac('sha256', $this->rawData, $apiKey, true));
    }

    /**
     * Checks if webhook is a test.
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->eventTopic === 'cases/test';
    }
}
