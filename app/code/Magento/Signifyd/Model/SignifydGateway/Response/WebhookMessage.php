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
     * @param array $data
     * @param string $eventTopic
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
}
