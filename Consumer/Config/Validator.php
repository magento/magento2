<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\Reflection\MethodsMap;
use Magento\Framework\MessageQueue\ConsumerInterface;

/**
 * Consumer config data validator.
 */
class Validator
{
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    public function __construct(MethodsMap $methodsMap)
    {
        $this->methodsMap = $methodsMap;
    }

    /**
     * Validate merged consumer config data.
     *
     * @param array $configData
     * @throws \LogicException
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerName => $consumerConfig) {
            $this->validateConsumerRequiredFields($consumerName, $consumerConfig);
            $this->validateHandlers($consumerConfig);
            $this->validateConsumerInstance($consumerConfig);
        }
    }

    /**
     * Make sure all required fields are present in the consumer item config.
     *
     * @param string $consumerName
     * @param array $consumerConfig
     */
    private function validateConsumerRequiredFields($consumerName, $consumerConfig)
    {
        $requiredFields = ['name', 'queue', 'handlers', 'consumerInstance', 'connection', 'maxMessages'];
        foreach ($requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $consumerConfig)) {
                throw new \LogicException(
                    sprintf("'%s' field must be specified for consumer '%s'", $fieldName, $consumerName)
                );
            }
        }
    }

    /**
     * Make sure that specified consumer instance is valid.
     *
     * @param array $consumerConfig
     */
    private function validateConsumerInstance($consumerConfig)
    {
        $consumerInstance = $consumerConfig['consumerInstance'];
        $implementedInterfaces = class_implements($consumerInstance);
        if (!in_array(ConsumerInterface::class, $implementedInterfaces)) {
            throw new \LogicException(
                sprintf(
                    "'%s' cannot be specified as 'consumerInstance' for '%s' consumer,"
                        . " unless it implements '%s' interface",
                    $consumerInstance,
                    $consumerConfig['name'],
                    ConsumerInterface::class
                )
            );
        }
    }

    /**
     * Validate handlers configuration for the specific consumer.
     *
     * @param array $consumerConfig
     */
    private function validateHandlers($consumerConfig)
    {
        $consumerName = $consumerConfig['name'];
        if (!is_array($consumerConfig['handlers'])) {
            throw new \LogicException(
                sprintf(
                    "'handlers' element must be an array for consumer '%s'",
                    $consumerName
                )
            );
        }
        foreach ($consumerConfig['handlers'] as $handler) {
            $this->validateHandler($handler, $consumerName);
        }
    }

    /**
     * Validate handler configuration.
     *
     * @param array $handler
     * @param string $consumerName
     */
    private function validateHandler($handler, $consumerName)
    {
        if (!isset($handler['type']) || !isset($handler['method'])) {
            throw new \LogicException(
                sprintf(
                    "'%s' consumer declaration is invalid. "
                    . "Every handler element must be an array. It must contain 'type' and 'method' elements.",
                    $consumerName
                )
            );
        }
        try {
            $this->methodsMap->getMethodParams($handler['type'], $handler['method']);
        } catch (\Exception $e) {
            throw new \LogicException(
                sprintf(
                    'Service method specified as handler for of consumer "%s" is not available. Given "%s"',
                    $consumerName,
                    $handler['type'] . '::' . $handler['method']
                )
            );
        }
    }
}
