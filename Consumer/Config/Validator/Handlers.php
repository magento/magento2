<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;
use Magento\Framework\Reflection\MethodsMap;

/**
 * Consumer config data validator for handlers.
 */
class Handlers implements ValidatorInterface
{
    /**
     * @var MethodsMap
     */
    private $methodsMap;

    /**
     * Initialize dependencies.
     *
     * @param MethodsMap $methodsMap
     */
    public function __construct(MethodsMap $methodsMap)
    {
        $this->methodsMap = $methodsMap;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerConfig) {
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
    }

    /**
     * Validate handler configuration.
     *
     * @param array $handler
     * @param string $consumerName
     * @return void
     * @throws \LogicException
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
