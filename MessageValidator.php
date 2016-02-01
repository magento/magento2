<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
            if (preg_match_all("/\\\\/", $messageDataType)) {
                $this->validateClassType($message, $messageDataType, $topic);
            } else {
                $this->validatePrimitiveType($message, $messageDataType, $topic);
            }
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
                        if (preg_match_all("/\\\\/", $paramType)) {
                            $this->validateClassType($message[$paramPosition], $paramType, $topic);
                        } else {
                            $this->validatePrimitiveType($message[$paramPosition], $paramType, $topic);
                        }
                    }
                } else {
                    if (isset($message[$paramName])) {
                        if (isset($message[$paramName])) {
                            if (preg_match_all("/\\\\/", $paramType)) {
                                $this->validateClassType($message[$paramName], $paramType, $topic);
                            } else {
                                $this->validatePrimitiveType($message[$paramName], $paramType, $topic);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param $message
     * @param $messageType
     * @param $topic
     * @return void
     */
    protected function validatePrimitiveType($message, $messageType, $topic)
    {
        if (gettype($message) !== $messageType) {
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
     * @param $message
     * @param $messageType
     * @param $topic
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
     * @param $message
     * @return string
     */
    private function getRealType($message)
    {
        return is_object($message) ? get_class($message) : gettype($message);
    }
}
