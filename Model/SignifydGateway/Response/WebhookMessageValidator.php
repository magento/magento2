<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Signifyd\Model\Config;

/**
 * Validates webhook message.
 *
 */
class WebhookMessageValidator
{

    /**
     * Allowed topic identifiers which will be sent in the X-SIGNIFYD-TOPIC header of the webhook.
     *
     * @var array
     */
    private $allowedTopicValues = [
        'cases/creation',
        'cases/rescore',
        'cases/review',
        'guarantees/completion',
        'cases/test'
    ];

    /**
     * @var array
     */
    private $errorMessages = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Validates webhook message.
     *
     * @param WebhookMessage $webhookMessage
     * @return bool
     */
    public function validate(WebhookMessage $webhookMessage)
    {
        if ($this->isValidTopic($webhookMessage->getEventTopic()) === false) {
            $this->errorMessages[] = 'Value of X-SIGNIFYD-TOPIC header is not allowed';
        }

        if (empty($webhookMessage->getData())) {
            $this->errorMessages[] = 'Webhook message is empty';
        }

        if ($this->isValidHash($webhookMessage) === false) {
            $this->errorMessages[] = 'X-SIGNIFYD-SEC-HMAC-SHA256 header verification fails';
        }

        return empty($this->errorMessages);

    }

    /**
     * Returns error message if validation fails
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return !empty($this->errorMessages) ? implode('; ', $this->errorMessages) : '';
    }

    /**
     * Checks if value of topic identifier is in allowed list
     *
     * @param string $topic topic identifier.
     * @return bool
     */
    private function isValidTopic($topic)
    {
        return in_array($topic, $this->allowedTopicValues);
    }

    /**
     * Verifies a webhook message has in fact come from SIGNIFYD.
     *
     * @param WebhookMessage $webhookMessage
     * @return bool
     */
    private function isValidHash(WebhookMessage $webhookMessage)
    {
        // In the case that this is a webhook test, the encoding ABCDE is allowed
        $apiKey = $webhookMessage->isTest() ? 'ABCDE' : $this->config->getApiKey();

        return $webhookMessage->getActualHash($apiKey) === $webhookMessage->getExpectedHash();
    }
}
