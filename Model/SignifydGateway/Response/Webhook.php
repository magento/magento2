<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

/**
 * Webhooks are messages sent by SIGNIFYD via HTTP POST to a url you configure on your
 * Notifications page in the SIGNIFYD settings.
 *
 * Webhook messages are sent when certain events occur in the life of an investigation.
 * They allow your application to receive pushed updates about a case, rather than poll SIGNIFYD for status changes.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/webhooks
 */
class Webhook
{
    /**
     * Webhook response body.
     *
     * @var array
     */
    private $body;

    /**
     * Event topic identifier.
     *
     * @var string
     */
    private $topic;

    /**
     * @param array $body
     * @param string $topic
     */
    public function __construct(
        array $body,
        $topic
    ) {
        $this->body = $body;
        $this->topic = $topic;
    }

    /**
     * Returns webhook body
     *
     * @return array
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns event topic identifier
     *
     * @return string
     */
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * Checks if webhook is a test
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->topic === 'cases/test';
    }
}
