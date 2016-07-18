<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\Reflection\MethodsMap;

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
        foreach ($configData as $consumerConfig) {
            $this->validateHandlers($consumerConfig);
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
        if (!isset($consumerConfig['handlers'])) {
            throw new \LogicException(
                sprintf(
                    "'handlers' array (at least empty one) must be specified for consumer '%s'",
                    $consumerName
                )
            );
        }
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
