<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;

/**
 * Reads request and produces webhook data object based on request params.
 */
class WebhookRequestReader
{
    /**
     * @var WebhookMessageValidator
     */
    private $webhookMessageValidator;

    /**
     * @var DecoderInterface
     */
    private $dataDecoder;

    /**
     * @var WebhookMessageFactory
     */
    private $webhookMessageFactory;

    /**
     * @param WebhookMessageValidator $webhookMessageValidator
     * @param DecoderInterface $decoder
     * @param WebhookMessageFactory $webhookMessageFactory
     */
    public function __construct(
        WebhookMessageValidator $webhookMessageValidator,
        DecoderInterface $decoder,
        WebhookMessageFactory $webhookMessageFactory
    ) {
        $this->webhookMessageValidator = $webhookMessageValidator;
        $this->dataDecoder = $decoder;
        $this->webhookMessageFactory = $webhookMessageFactory;
    }

    /**
     * Returns webhook message data object.
     *
     * @param WebhookRequest $request
     * @return WebhookMessage
     * @throws WebhookException if data validation fails
     */
    public function read(WebhookRequest $request)
    {
        $hash = $request->getHeader('X-SIGNIFYD-SEC-HMAC-SHA256');
        $eventTopic = $request->getHeader('X-SIGNIFYD-TOPIC');
        $rawData = $request->getBody();

        try {
            $decodedData = $this->dataDecoder->decode($rawData);
        } catch (\Exception $e) {
            throw new WebhookException(
                'Webhook request body is not valid JSON: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $webhookMessage = $this->webhookMessageFactory->create(
            [
                'rawData' => $rawData,
                'data' => $decodedData,
                'eventTopic' => $eventTopic,
                'expectedHash' => $hash
            ]
        );

        if (!$this->webhookMessageValidator->validate($webhookMessage)) {
            throw new WebhookException(
                $this->webhookMessageValidator->getErrorMessage()
            );
        }

        return $webhookMessage;
    }
}
