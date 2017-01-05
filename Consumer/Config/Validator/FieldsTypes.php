<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;

/**
 * Consumer config data validator for fields types.
 */
class FieldsTypes implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerName => $consumerConfig) {
            $this->validateConsumerFieldsTypes($consumerName, $consumerConfig);
        }
    }

    /**
     * Make sure types of all fields in the consumer item config are correct.
     *
     * @param string $consumerName
     * @param array $consumerConfig
     * @return void
     * @throws \LogicException
     */
    private function validateConsumerFieldsTypes($consumerName, $consumerConfig)
    {
        $fields = [
            'name' => 'string',
            'queue' => 'string',
            'handlers' => 'array',
            'consumerInstance' => 'string',
            'connection' => 'string'
        ];
        foreach ($fields as $fieldName => $expectedType) {
            $actualType = gettype($consumerConfig[$fieldName]);
            if ($actualType !== $expectedType) {
                throw new \LogicException(
                    sprintf(
                        "Type of '%s' field specified in configuration of '%s' consumer is invalid. "
                        . "Given '%s', '%s' was expected.",
                        $fieldName,
                        $consumerName,
                        $actualType,
                        $expectedType
                    )
                );
            }
        }
        if (null !== $consumerConfig['maxMessages'] && !is_numeric($consumerConfig['maxMessages'])) {
            throw new \LogicException(
                sprintf(
                    "Type of 'maxMessages' field specified in configuration of '%s' consumer is invalid. "
                    . "Given '%s', '%s' was expected.",
                    $consumerName,
                    gettype($consumerConfig['maxMessages']),
                    'int|null'
                )
            );
        }
    }
}
