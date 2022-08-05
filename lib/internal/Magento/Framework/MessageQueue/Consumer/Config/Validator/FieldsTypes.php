<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @inheritdoc
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
        $additionalNumericFields = ['maxMessages', 'maxIdleTime', 'sleep'];
        foreach ($additionalNumericFields as $fieldName) {
            if (null !== $consumerConfig[$fieldName] && !is_numeric($consumerConfig[$fieldName])) {
                throw new \LogicException(
                    sprintf(
                        "Type of '%s' field specified in configuration of '%s' consumer is invalid. "
                        . "Given '%s', '%s' was expected.",
                        $fieldName,
                        $consumerName,
                        gettype($consumerConfig[$fieldName]),
                        'int|null'
                    )
                );
            }
        }
        if (null !== $consumerConfig['onlySpawnWhenMessageAvailable']
            && !is_bool($consumerConfig['onlySpawnWhenMessageAvailable'])
        ) {
            throw new \LogicException(
                sprintf(
                    "Type of 'onlySpawnWhenMessageAvailable' field specified in configuration of '%s' "
                    . "consumer is invalid. Given '%s', '%s' was expected.",
                    $consumerName,
                    gettype($consumerConfig['onlySpawnWhenMessageAvailable']),
                    'boolean|null'
                )
            );
        }
    }
}
