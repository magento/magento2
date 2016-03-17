<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class MessageValidator to validate message with topic schema
 *
 */
class MessageValidator
{
    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * Initialize dependencies.
     *
     * @param QueueConfig $queueConfig
     */
    public function __construct(
        QueueConfig $queueConfig
    ) {
        $this->queueConfig = $queueConfig;
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
        $topicConfig = $this->queueConfig->getTopic($topic);
        if ($topicConfig === null) {
            throw new LocalizedException(new Phrase('Specified topic "%topic" is not declared.', ['topic' => $topic]));
        }
        return $requestType
            ? $topicConfig[QueueConfig::TOPIC_SCHEMA]
            : $topicConfig[QueueConfig::TOPIC_RESPONSE_SCHEMA];
    }

    /**
     * Validate message according to the format associated with its topic
     *
     * @param string $topic
     * @param mixed $message
     * @param bool $requestType
     * @return void
     * @throws InvalidArgumentException
     */
    public function validate($topic, $message, $requestType = true)
    {
        $topicSchema = $this->getTopicSchema($topic, $requestType);
        if ($topicSchema[QueueConfig::TOPIC_SCHEMA_TYPE] == QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT) {
            $messageDataType = $topicSchema[QueueConfig::TOPIC_SCHEMA_VALUE];
            $this->validateMessage($message, $messageDataType, $topic);
        } else {
            /** Validate message according to the method signature associated with the message topic */
            $message = (array)$message;
            $isIndexedArray = array_keys($message) === range(0, count($message) - 1);
            foreach ($topicSchema[QueueConfig::TOPIC_SCHEMA_VALUE] as $methodParameterMeta) {
                $paramName = $methodParameterMeta[QueueConfig::SCHEMA_METHOD_PARAM_NAME];
                $paramType = $methodParameterMeta[QueueConfig::SCHEMA_METHOD_PARAM_TYPE];
                if ($isIndexedArray) {
                    $paramPosition = $methodParameterMeta[QueueConfig::SCHEMA_METHOD_PARAM_POSITION];
                    if (isset($message[$paramPosition])) {
                        $this->validateMessage($message[$paramPosition], $paramType, $topic);
                    }
                } else {
                    if (isset($message[$paramName])) {
                        if (isset($message[$paramName])) {
                            $this->validateMessage($message[$paramName], $paramType, $topic);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $message
     * @param string $messageType
     * @param string $topic
     * @return void
     */
    protected function validateMessage($message, $messageType, $topic)
    {
        if (preg_match_all("/\\\\/", $messageType)) {
            $this->validateClassType($message, $messageType, $topic);
        } else {
            $this->validatePrimitiveType($message, $messageType, $topic);
        }
    }

    /**
     * @param string $message
     * @param string $messageType
     * @param string $topic
     * @return void
     */
    protected function validatePrimitiveType($message, $messageType, $topic)
    {
        if ($this->getRealType($message) !== $messageType) {
            throw new InvalidArgumentException(
                new Phrase(
                    'Data in topic "%topic" must be of type "%expectedType". '
                    . '"%actualType" given.',
                    [
                        'topic' => $topic,
                        'expectedType' => $messageType,
                        'actualType' => $this->getRealType($message)
                    ]
                )
            );
        }
    }

    /**
     * @param string $message
     * @param string $messageType
     * @param string $topic
     * @return void
     */
    protected function validateClassType($message, $messageType, $topic)
    {
        if (!($message instanceof $messageType)) {
            throw new InvalidArgumentException(
                new Phrase(
                    'Data in topic "%topic" must be of type "%expectedType". '
                    . '"%actualType" given.',
                    [
                        'topic' => $topic,
                        'expectedType' => $messageType,
                        'actualType' => $this->getRealType($message)
                    ]
                )
            );
        }
    }

    /**
     * @param string $message
     * @return string
     */
    private function getRealType($message)
    {
        $type = is_object($message) ? get_class($message) : gettype($message);
        return $type == "integer" ? "int" : $type;
    }
}
