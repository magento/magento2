<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\SignifydGateway\Response;

use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Factory produces webhook data object based on request params.
 */
class WebhookFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @var DecoderInterface
     */
    private $dataDecoder;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ResponseValidator $responseValidator
     * @param DecoderInterface $decoder
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ResponseValidator $responseValidator,
        DecoderInterface $decoder
    ) {
        $this->objectManager = $objectManager;
        $this->responseValidator = $responseValidator;
        $this->dataDecoder = $decoder;
    }

    /**
     * Create webhook data object.
     *
     * @param string $rawResponseBody
     * @param string $hash Base64 encoded output of the HMAC SHA256 encoding of the JSON body of the message.
     * @param string $topic event topic identifier.
     * @return Webhook
     * @throws WebhookException if data validation fails
     */
    public function create($rawResponseBody, $hash, $topic)
    {

        if (!$this->responseValidator->validate($rawResponseBody, $hash, $topic)) {
            throw new WebhookException(
                $this->responseValidator->getErrorMessage()
            );
        }

        try {
            $decodedResponseBody = $this->dataDecoder->decode($rawResponseBody);
        } catch (\Exception $e) {
            throw new WebhookException(
                'Webhook message body is not valid JSON: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }

        return $this->objectManager->create(
            Webhook::class,
            ['topic' => $topic, 'body' => $decodedResponseBody]
        );
    }
}
