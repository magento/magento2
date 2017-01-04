<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Signifyd\Model\Config;

/**
 * Validates webhook response.
 *
 */
class ResponseValidator
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
     * Validates webhook response.
     *
     * @param string $rawResponseBody
     * @param string $hash Base64 encoded output of the HMAC SHA256 encoding of the JSON body of the message.
     * @param string $topic event topic identifier.
     * @return bool
     */
    public function validate($rawResponseBody, $hash, $topic)
    {
        if ($this->isValidTopic($topic) === false) {
            $this->errorMessages[] = 'Value of X-SIGNIFYD-TOPIC header is not allowed';
        }

        if (empty($rawResponseBody)) {
            $this->errorMessages[] = 'Webhook message is empty';
        }

        if ($this->isValidHmacSha256($rawResponseBody, $hash, $topic) === false) {
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
     * @param string $rawResponseBody
     * @param string $hash X-SIGNIFYD-SEC-HMAC-SHA256 header is included in each webhook POST message.
     * @param string $topic topic identifier.
     * @return bool
     */
    private function isValidHmacSha256($rawResponseBody, $hash, $topic)
    {
        // In the case that this is a webhook test, the encoding ABCDE is allowed
        $apiKey = $topic == 'cases/test' ? 'ABCDE' : $this->config->getApiKey();
        $check = base64_encode(hash_hmac('sha256', $rawResponseBody, $apiKey, true));

        return $check === $hash;
    }
}
