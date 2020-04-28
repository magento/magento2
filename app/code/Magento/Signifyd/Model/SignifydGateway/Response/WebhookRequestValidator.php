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
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class WebhookRequestValidator
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
     * @var Config
     */
    private $config;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @param Config $config
     * @param DecoderInterface $decoder
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
     */
    private function isValidHash($eventTopic, $body, $hash)
    {
        // In the case that this is a webhook test, the encoding ABCDE is allowed
        $apiKey = $eventTopic == 'cases/test' ? 'ABCDE' : $this->config->getApiKey();
        $actualHash = base64_encode(hash_hmac('sha256', $body, $apiKey, true));

        return $hash === $actualHash;
    }
}
