<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Reflection\MethodsMap;

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
     * Initialize dependencies.

     * @param MethodsMap $methodsMap
     */
    public function __construct(
        MethodsMap $methodsMap
    ) {
        $this->methodsMap = $methodsMap;
    }

    /**
     * @param string $schemaType
     * @param string $schemaMethod
     * @param string $topicName
     * @return void
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
     * @param string $serviceName
     * @param string $methodName
     * @param string $consumerName
     * @return void
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
     * @param $topics
     * @param $topicName
     */
    public function validateBindTopic($topics, $topicName)
    {
        if (!array_key_exists($topicName, $topics)) {
            throw new \LogicException(
                sprintf('Topic "%s" declared in binds must be defined in topics', $topicName)
            );
        }
    }

    /**
     * @param $publishers
     * @param $publisherName
     * @param $topicName
     */
    public function validateTopicPublisher($publishers, $publisherName, $topicName)
    {
        if (!array_key_exists($publisherName, $publishers)) {
            throw new \LogicException(
                sprintf(
                    'Publisher "%s", specified in env.php for topic "%s" is not declared.',
                    $publisherName,
                    $topicName
                )
            );
        }
    }

}
