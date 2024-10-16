<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer;
use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;

/**
 * Consumer config data validator for consumer instance.
 */
class ConsumerInstance implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerConfig) {
            $this->validateConsumerInstance($consumerConfig);
        }
    }

    /**
     * Make sure that specified consumer instance is valid.
     *
     * @param array $consumerConfig
     * @return void
     * @throws \LogicException
     */
    private function validateConsumerInstance($consumerConfig)
    {
        $consumerInstance = $consumerConfig['consumerInstance'];
        if ($consumerInstance == ConsumerInterface::class) {
            return;
        }
        if (!class_exists($consumerInstance)) {
            throw new \LogicException(
                sprintf(
                    "'%s' does not exist and thus cannot be used as 'consumerInstance' for '%s' consumer.",
                    $consumerInstance,
                    $consumerConfig['name'],
                )
            );
        }
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
}
