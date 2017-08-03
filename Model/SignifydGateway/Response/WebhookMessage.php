<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

/**
 * Webhooks are messages sent by SIGNIFYD via HTTP POST to a url you configure on your
 * Notifications page in the SIGNIFYD settings.
 *
 * WebhookMessage messages are sent when certain events occur in the life of an investigation.
 * They allow your application to receive pushed updates about a case, rather than poll SIGNIFYD for status changes.
 *
 * @see https://www.signifyd.com/docs/api/#/reference/webhooks
 * @since 2.2.0
 */
class WebhookMessage
{
    /**
     * Decoded webhook request body.
     *
     * @var array
     * @since 2.2.0
     */
    private $data;

    /**
     * Event topic identifier.
     *
     * @var string
     * @since 2.2.0
     */
    private $eventTopic;

    /**
     * @param array $data
     * @param string $eventTopic
     * @since 2.2.0
     */
    public function __construct(
        array $data,
        $eventTopic
    ) {
        $this->data = $data;
        $this->eventTopic = $eventTopic;
    }

    /**
     * Returns decoded webhook request body.
     *
     * @return array
     * @since 2.2.0
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns event topic identifier.
     *
     * @return string
     * @since 2.2.0
     */
    public function getEventTopic()
    {
        return $this->eventTopic;
    }
}
