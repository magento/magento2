<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\Config\Data as QueueConfig;
use Magento\Framework\MessageQueue\Config\Converter as QueueConfigConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\ServicePayloadConverterInterface;

/**
 * Class which provides encoding and decoding capabilities for MessageQueue messages.
 */
class MessageEncoder
{
    const DIRECTION_ENCODE = 'encode';
    const DIRECTION_DECODE = 'decode';

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
        $convertedMessage = $this->convertMessage($topic, $message, self::DIRECTION_ENCODE);
        return $this->jsonEncoder->encode($convertedMessage);
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
        try {
            $decodedMessage = $this->jsonDecoder->decode($message);
        } catch (\Exception $e) {
            throw new LocalizedException(new Phrase("Error occurred during message decoding."));
        }
        return $this->convertMessage($topic, $decodedMessage, self::DIRECTION_DECODE);
    }

    /**
     * Identify message data schema by topic.
     *
     * @param string $topic
     * @return array
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

    /**
     * Convert message according to the format associated with its topic using provided converter.
     *
     * @param string $topic
     * @param mixed $message
     * @param string $direction
     * @return mixed
     * @throws LocalizedException
     */
    protected function convertMessage($topic, $message, $direction)
    {
        $topicSchema = $this->getTopicSchema($topic);
        if ($topicSchema[QueueConfigConverter::TOPIC_SCHEMA_TYPE] == QueueConfigConverter::TOPIC_SCHEMA_TYPE_OBJECT) {
            /** Convert message according to the data interface associated with the message topic */
            $messageDataType = $topicSchema[QueueConfigConverter::TOPIC_SCHEMA_VALUE];
            try {
                $convertedMessage = $this->getConverter($direction)->convertValue($message, $messageDataType);
            } catch (LocalizedException $e) {
                throw new LocalizedException(
                    new Phrase(
                        'Message with topic "%topic" must be an instance of "%class".',
                        ['topic' => $topic, 'class' => $messageDataType]
                    )
                );
            }
        } else {
            /** Convert message according to the method signature associated with the message topic */
            $message = (array)$message;
            $isIndexedArray = array_keys($message) === range(0, count($message) - 1);
            $convertedMessage = [];
            /** Message schema type is defined by method signature */
            foreach ($topicSchema[QueueConfigConverter::TOPIC_SCHEMA_VALUE] as $methodParameterMeta) {
                $paramName = $methodParameterMeta[QueueConfigConverter::SCHEMA_METHOD_PARAM_NAME];
                $paramType = $methodParameterMeta[QueueConfigConverter::SCHEMA_METHOD_PARAM_TYPE];
                if ($isIndexedArray) {
                    /** Encode parameters according to their positions in method signature */
                    $paramPosition = $methodParameterMeta[QueueConfigConverter::SCHEMA_METHOD_PARAM_POSITION];
                    if (isset($message[$paramPosition])) {
                        $convertedMessage[$paramName] = $this->getConverter($direction)
                            ->convertValue($message[$paramPosition], $paramType);
                    }
                } else {
                    /** Encode parameters according to their names in method signature */
                    if (isset($message[$paramName])) {
                        $convertedMessage[$paramName] = $this->getConverter($direction)
                            ->convertValue($message[$paramName], $paramType);
                    }
                }

                /** Ensure that all required params were passed */
                if ($methodParameterMeta[QueueConfigConverter::SCHEMA_METHOD_PARAM_IS_REQUIRED]
                    && !isset($convertedMessage[$paramName])
                ) {
                    throw new LocalizedException(
                        new Phrase(
                            'Data item corresponding to "%param" of "%method" must be specified '
                            . 'in the message with topic "%topic".',
                            [
                                'topic' => $topic,
                                'param' => $paramName,
                                'method' => $topicSchema[QueueConfigConverter::TOPIC_SCHEMA_METHOD_NAME]
                            ]
                        )
                    );
                }
            }
        }
        return $convertedMessage;
    }

    /**
     * Get value converter based on conversion direction.
     *
     * @param string $direction
     * @return ServicePayloadConverterInterface
     */
    protected function getConverter($direction)
    {
        return ($direction == self::DIRECTION_ENCODE) ? $this->dataObjectEncoder : $this->dataObjectDecoder;
    }
}
