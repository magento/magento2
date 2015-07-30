<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

use Magento\Framework\Amqp\Config\Data as QueueConfig;
use Magento\Framework\Amqp\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class which provides encoding and decoding capabilities for AMQP messages.
 */
class MessageEncoder
{
    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var \Magento\Framework\Webapi\ServiceOutputProcessor
     */
    private $dataObjectEncoder;

    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor
     */
    private $dataObjectDecoder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $dataObjectEncoder
     * @param \Magento\Framework\Webapi\ServiceInputProcessor $dataObjectDecoder
     */
    public function __construct(
        QueueConfig $queueConfig,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Webapi\ServiceOutputProcessor $dataObjectEncoder,
        \Magento\Framework\Webapi\ServiceInputProcessor $dataObjectDecoder
    ) {
        $this->queueConfig = $queueConfig;
        $this->dataObjectEncoder = $dataObjectEncoder;
        $this->dataObjectDecoder = $dataObjectDecoder;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Encode message content based on current topic.
     *
     * @param string $topic
     * @param mixed $message
     * @return string
     * @throws LocalizedException
     */
    public function encode($topic, $message)
    {
        $messageDataType = $this->getTopicSchema($topic);
        if (!is_array($message)) {
            $isMessageValid = $message instanceof $messageDataType;
        } else {
            $messageItemDataType = substr($messageDataType, 0, -2);
            $isMessageValid = empty($message) || (reset($message) instanceof $messageItemDataType);
        }
        if (!$isMessageValid) {
            throw new LocalizedException(
                new Phrase(
                    'Message with topic "%topic" must be an instance of "%class".',
                    ['topic' => $topic, 'class' => $messageDataType]
                )
            );
        }
        return $this->jsonEncoder->encode($this->dataObjectEncoder->convertValue($message, $messageDataType));
    }

    /**
     * Decode message content based on current topic.
     *
     * @param string $topic
     * @param string $message
     * @return mixed
     * @throws LocalizedException
     */
    public function decode($topic, $message)
    {
        $messageDataType = $this->getTopicSchema($topic);
        try {
            $decodedJson = $this->jsonDecoder->decode($message);
        } catch (\Exception $e) {
            throw new LocalizedException(new Phrase("Error occurred during message decoding."));
        }
        return $this->dataObjectDecoder->convertValue($decodedJson, $messageDataType);
    }

    /**
     * Identify message data schema by topic.
     *
     * @param string $topic
     * @return string
     * @throws LocalizedException
     */
    protected function getTopicSchema($topic)
    {
        $queueConfig = $this->queueConfig->get();
        if (isset($queueConfig[QueueConfigConverter::TOPICS][$topic])) {
            return $queueConfig[QueueConfigConverter::TOPICS][$topic][QueueConfigConverter::TOPIC_SCHEMA];
        }
        throw new LocalizedException(new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topic]));
    }
}