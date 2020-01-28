<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Class MessageValidator to validate message with topic schema
 *
 */
class MessageValidator
{
    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

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
        if ($topicSchema['schema_type'] == CommunicationConfig::TOPIC_REQUEST_TYPE_CLASS) {
            $messageDataType = $topicSchema['schema_value'];
            $this->validateMessage($message, $messageDataType, $topic);
        } else {
            /** Validate message according to the method signature associated with the message topic */
            $message = (array)$message;
            $isIndexedArray = array_keys($message) === range(0, count($message) - 1);
            foreach ($topicSchema['schema_value'] as $methodParameterMeta) {
                $paramName = $methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_NAME];
                $paramType = $methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_TYPE];
                if ($isIndexedArray) {
                    $paramPosition = $methodParameterMeta[CommunicationConfig::SCHEMA_METHOD_PARAM_POSITION];
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
        $compareType = $messageType;
        $realType = $this->getRealType($message);
        if ($realType == 'array' && count($message) == 0) {
            return;
        } elseif ($realType == 'array' && count($message) > 0) {
            $realType = $this->getRealType($message[0]);
            $compareType = preg_replace('/\[\]/', '', $messageType);
        }
        if ($realType !== $compareType) {
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
        $origMessage = $message;
        $compareType = $messageType;
        $realType = $this->getRealType($message);
        if ($realType == 'array' && count($message) == 0) {
            return;
        } elseif ($realType == 'array' && count($message) > 0) {
            $message = $message[0];
            $compareType = preg_replace('/\[\]/', '', $messageType);
        }
        if (!($message instanceof $compareType)) {
            throw new InvalidArgumentException(
                new Phrase(
                    'Data in topic "%topic" must be of type "%expectedType". '
                    . '"%actualType" given.',
                    [
                        'topic' => $topic,
                        'expectedType' => $messageType,
                        'actualType' => $this->getRealType($origMessage)
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
        $type = $type == 'boolean' ? 'bool' : $type;
        $type = $type == 'double' ? 'float' : $type;
        return $type == "integer" ? "int" : $type;
    }

    /**
     * Get communication config.
     *
     * @return CommunicationConfig
     *
     * @deprecated 102.0.3
     */
    private function getCommunicationConfig()
    {
        if ($this->communicationConfig === null) {
            $this->communicationConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(
                CommunicationConfig::class
            );
        }
        return $this->communicationConfig;
    }
}
