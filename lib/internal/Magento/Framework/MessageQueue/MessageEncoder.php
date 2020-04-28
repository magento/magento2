<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Webapi\ServicePayloadConverterInterface;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Class which provides encoding and decoding capabilities for MessageQueue messages.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessageEncoder
{
    const DIRECTION_ENCODE = 'encode';
    const DIRECTION_DECODE = 'decode';

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
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Webapi\ServiceOutputProcessor $dataObjectEncoder
     * @param \Magento\Framework\Webapi\ServiceInputProcessor $dataObjectDecoder
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        QueueConfig $queueConfig,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Webapi\ServiceOutputProcessor $dataObjectEncoder,
        \Magento\Framework\Webapi\ServiceInputProcessor $dataObjectDecoder
    ) {
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
     * @param bool $requestType
     * @return string
     * @throws LocalizedException
     */
    public function encode($topic, $message, $requestType = true)
    {
        $convertedMessage = $this->convertMessage($topic, $message, self::DIRECTION_ENCODE, $requestType);
        return $this->jsonEncoder->encode($convertedMessage);
    }

    /**
     * Decode message content based on current topic.
     *
     * @param string $topic
     * @param string $message
     * @param bool $requestType
     * @return mixed
     * @throws LocalizedException
     */
    public function decode($topic, $message, $requestType = true)
    {
        try {
            $decodedMessage = $this->jsonDecoder->decode($message);
        } catch (\Exception $e) {
            throw new LocalizedException(new Phrase("Error occurred during message decoding."));
        }
        return $this->convertMessage($topic, $decodedMessage, self::DIRECTION_DECODE, $requestType);
    }

    /**
     * Identify message data schema by topic.
     *
     * @param string $topic
     * @param bool $requestType
     * @return array
     * @throws LocalizedException
     */
    protected function getTopicSchema($topic, $requestType)
    {
        $topicConfig = $this->getCommunicationConfig()->getTopic($topic);
        if ($topicConfig === null) {
            throw new LocalizedException(new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topic]));
        }
        if ($requestType) {
            return [
                'schema_type' => $topicConfig[CommunicationConfig::TOPIC_REQUEST_TYPE],
                'schema_value' => $topicConfig[CommunicationConfig::TOPIC_REQUEST]
            ];
        } else {
            return [
                'schema_type' => isset($topicConfig[CommunicationConfig::TOPIC_RESPONSE])
                    ? CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS
                    : null,
                'schema_value' => $topicConfig[CommunicationConfig::TOPIC_RESPONSE]
            ];
        }
    }

    /**
     * Convert message according to the format associated with its topic using provided converter.
     *
     * @param string $topic
     * @param mixed $message
     * @param string $direction
     * @param bool $requestType
     * @return mixed
     * @throws LocalizedException
     */
    protected function convertMessage($topic, $message, $direction, $requestType)
    {
        $topicSchema = $this->getTopicSchema($topic, $requestType);
        if ($topicSchema['schema_type'] == CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS) {
            /** Convert message according to the data interface associated with the message topic */
            $messageDataType = $topicSchema[QueueConfig::TOPIC_SCHEMA_VALUE];
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
            foreach ($topicSchema['schema_value'] as $methodParameterMeta) {
                $paramName = $methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_NAME];
                $paramType = $methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_TYPE];
                if ($isIndexedArray) {
                    /** Encode parameters according to their positions in method signature */
                    $paramPosition = $methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_POSITION];
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
                if ($methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_IS_REQUIRED]
                    && !isset($convertedMessage[$paramName])
                ) {
                    throw new LocalizedException(
                        new Phrase(
                            'Data item corresponding to "%param" must be specified '
                            . 'in the message with topic "%topic".',
                            [
                                'topic' => $topic,
                                'param' => $paramName
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

    /**
     * Get communication config.
     *
     * @return CommunicationConfig
     *
     * @deprecated 102.0.5
     */
    private function getCommunicationConfig()
    {
        if ($this->communicationConfig === null) {
            $this->communicationConfig = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(CommunicationConfig::class);
        }
        return $this->communicationConfig;
    }
}
