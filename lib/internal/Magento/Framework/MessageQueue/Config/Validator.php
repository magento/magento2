<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\Reflection\TypeProcessor;

/**
 * Communication configuration validator.
 */
class Validator
{
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Initialize dependencies.
     *
     * @param TypeProcessor $typeProcessor
     * @param MethodsMap $methodsMap
     */
    public function __construct(
        TypeProcessor $typeProcessor,
        MethodsMap $methodsMap
    ) {
        $this->typeProcessor = $typeProcessor;
        $this->methodsMap = $methodsMap;
    }

    /**
     * Validate schema Method Type
     *
     * @param string $schemaType
     * @param string $schemaMethod
     * @param string $topicName
     * @return void
     * @throws \LogicException
     */
    public function validateSchemaMethodType($schemaType, $schemaMethod, $topicName)
    {
        try {
            $this->methodsMap->getMethodParams($schemaType, $schemaMethod);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Service method specified for topic "%s" is not available. Given "%s"',
                    $topicName,
                    $schemaType . '::' . $schemaMethod
                )
            );
        }
    }

    /**
     * Validate handler type
     *
     * @param string $serviceName
     * @param string $methodName
     * @param string $consumerName
     * @return void
     * @throws \LogicException
     */
    public function validateHandlerType($serviceName, $methodName, $consumerName)
    {
        try {
            $this->methodsMap->getMethodParams($serviceName, $methodName);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Service method specified in handler for consumer "%s"'
                    . ' is not available. Given "%s"',
                    $consumerName,
                    $serviceName . '::' . $methodName
                )
            );
        }
    }

    /**
     * Validate topic in a bind
     *
     * @param string[] $topics
     * @param string $topicName
     * @return void
     * @throws \LogicException
     */
    public function validateBindTopic($topics, $topicName)
    {
        $isDefined = false;
        if (strpos($topicName, '#') === false && strpos($topicName, '*') === false) {
            if (in_array($topicName, $topics)) {
                $isDefined = true;
            }
        } else {
            $pattern = $this->buildWildcardPattern($topicName);
            if (count(preg_grep($pattern, $topics))) {
                $isDefined = true;
            }
        }
        if (!$isDefined) {
            throw new \LogicException(
                sprintf('Topic "%s" declared in binds must be defined in topics', $topicName)
            );
        }
    }

    /**
     * Construct perl regexp pattern for matching topic names from wildcard key.
     *
     * @param string $wildcardKey
     * @return string
     */
    public function buildWildcardPattern($wildcardKey)
    {
        $pattern = '/^' . str_replace('.', '\.', $wildcardKey);
        $pattern = str_replace('#', '.+', $pattern);
        $pattern = str_replace('*', '[^\.]+', $pattern);
        if (strpos($wildcardKey, '#') === strlen($wildcardKey)) {
            $pattern .= '/';
        } else {
            $pattern .= '$/';
        }

        return $pattern;
    }

    /**
     * Validate publisher in the topic
     *
     * @param string[] $publishers
     * @param string $publisherName
     * @param string $topicName
     * @return void
     * @throws \LogicException
     */
    public function validateTopicPublisher($publishers, $publisherName, $topicName)
    {
        if (!in_array($publisherName, $publishers)) {
            throw new \LogicException(
                sprintf(
                    'Publisher "%s", specified in env.php for topic "%s" is not declared.',
                    $publisherName,
                    $topicName
                )
            );
        }
    }

    /**
     * Validate response schema type
     *
     * @param string $responseSchema
     * @param string $topicName
     * @return void
     * @throws \LogicException
     */
    public function validateResponseSchemaType($responseSchema, $topicName)
    {
        try {
            $this->validateType($responseSchema);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Response schema definition for topic "%s" should reference existing type or service class. '
                    . 'Given "%s"',
                    $topicName,
                    $responseSchema
                )
            );
        }
    }

    /**
     * Validate schema type
     *
     * @param string $schema
     * @param string $topicName
     * @return void
     * @throws \LogicException
     */
    public function validateSchemaType($schema, $topicName)
    {
        try {
            $this->validateType($schema);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Schema definition for topic "%s" should reference existing type or service class. '
                    . 'Given "%s"',
                    $topicName,
                    $schema
                )
            );
        }
    }

    /**
     * Ensure that specified type is either a simple type or a valid service data type.
     *
     * @param string $typeName
     * @return $this
     * @throws \Exception In case when type is invalid
     */
    protected function validateType($typeName)
    {
        if ($this->typeProcessor->isTypeSimple($typeName)) {
            return $this;
        }
        if ($this->typeProcessor->isArrayType($typeName)) {
            $arrayItemType = $this->typeProcessor->getArrayItemType($typeName);
            $this->methodsMap->getMethodsMap($arrayItemType);
        } else {
            $this->methodsMap->getMethodsMap($typeName);
        }
        return $this;
    }
}
