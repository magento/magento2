<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;

/**
 * Reads request and produces webhook message data object based on request params.
 * @since 2.2.0
 */
class WebhookMessageReader
{
    /**
     * @var DecoderInterface
     * @since 2.2.0
     */
    private $dataDecoder;

    /**
     * @var WebhookMessageFactory
     * @since 2.2.0
     */
    private $webhookMessageFactory;

    /**
     * @param DecoderInterface $decoder
     * @param WebhookMessageFactory $webhookMessageFactory
     * @since 2.2.0
     */
    public function __construct(
        DecoderInterface $decoder,
        WebhookMessageFactory $webhookMessageFactory
    ) {
        $this->dataDecoder = $decoder;
        $this->webhookMessageFactory = $webhookMessageFactory;
    }

    /**
     * Returns webhook message data object.
     *
     * @param WebhookRequest $request
     * @return WebhookMessage
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function read(WebhookRequest $request)
    {
        try {
            $decodedData = $this->dataDecoder->decode($request->getBody());
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                'Webhook request body is not valid JSON: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        $webhookMessage = $this->webhookMessageFactory->create(
            [
                'data' => $decodedData,
                'eventTopic' => $request->getEventTopic()
            ]
        );

        return $webhookMessage;
    }
}
