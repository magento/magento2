<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;
use Magento\Signifyd\Model\Config;

/**
 * Validates webhook request.
 *
 * @since 2.2.0
 */
class WebhookRequestValidator
{
    /**
     * Allowed topic identifiers which will be sent in the X-SIGNIFYD-TOPIC header of the webhook.
     *
     * @var array
     * @since 2.2.0
     */
    private $allowedTopicValues = [
        'cases/creation',
        'cases/rescore',
        'cases/review',
        'guarantees/completion',
        'cases/test'
    ];

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * @var DecoderInterface
     * @since 2.2.0
     */
    private $decoder;

    /**
     * @param Config $config
     * @param DecoderInterface $decoder
     * @since 2.2.0
     */
    public function __construct(
        Config $config,
        DecoderInterface $decoder
    ) {
        $this->config = $config;
        $this->decoder = $decoder;
    }

    /**
     * Validates webhook request.
     *
     * @param WebhookRequest $webhookRequest
     * @return bool
     * @since 2.2.0
     */
    public function validate(WebhookRequest $webhookRequest)
    {
        $body = $webhookRequest->getBody();
        $eventTopic = $webhookRequest->getEventTopic();
        $hash = $webhookRequest->getHash();

        return $this->isValidTopic($eventTopic)
            && $this->isValidBody($body)
            && $this->isValidHash($eventTopic, $body, $hash);
    }

    /**
     * Checks if value of topic identifier is in allowed list
     *
     * @param string $topic topic identifier.
     * @return bool
     * @since 2.2.0
     */
    private function isValidTopic($topic)
    {
        return in_array($topic, $this->allowedTopicValues);
    }

    /**
     * Verifies a webhook request body is valid JSON and not empty.
     *
     * @param string $body
     * @return bool
     * @since 2.2.0
     */
    private function isValidBody($body)
    {
        try {
            $decodedBody = $this->decoder->decode($body);
        } catch (\Exception $e) {
            return false;
        }

        return !empty($decodedBody);
    }

    /**
     * Verifies a webhook request has in fact come from SIGNIFYD.
     *
     * @param string $eventTopic
     * @param string $body
     * @param string $hash
     * @return bool
     * @since 2.2.0
     */
    private function isValidHash($eventTopic, $body, $hash)
    {
        // In the case that this is a webhook test, the encoding ABCDE is allowed
        $apiKey = $eventTopic == 'cases/test' ? 'ABCDE' : $this->config->getApiKey();
        $actualHash = base64_encode(hash_hmac('sha256', $body, $apiKey, true));

        return $hash === $actualHash;
    }
}
