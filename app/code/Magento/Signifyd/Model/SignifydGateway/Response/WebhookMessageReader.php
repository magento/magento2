<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;

/**
 * Reads request and produces webhook message data object based on request params.
 */
class WebhookMessageReader
{
    /**
     * @var DecoderInterface
     */
    private $dataDecoder;

    /**
     * @var WebhookMessageFactory
     */
    private $webhookMessageFactory;

    /**
     * @param DecoderInterface $decoder
     * @param WebhookMessageFactory $webhookMessageFactory
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
