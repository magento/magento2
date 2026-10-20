<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\Validator;

use Magento\Framework\MessageQueue\Topology\Config\ValidatorInterface;

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
        foreach ($configData as $name => $config) {
            if ($config['type'] === 'queue') {
                $this->validateQueueFieldsTypes($name, $config);
                continue;
            }
            $this->validateFieldsTypes($name, $config);
        }
    }

    /**
     * Make sure types of all fields in the exchange item config are correct.
     *
     * @param string $exchangeName
     * @param array $exchangeConfig
     * @return void
     * @throws \LogicException
     */
    private function validateFieldsTypes($exchangeName, $exchangeConfig)
    {
        $fields = [
            'name' => ['type' => 'string', 'value' => null],
            'type' => ['type' => 'string', 'value' => ['topic']],
            'connection' => ['type' => 'string', 'value' => null],
            'durable' => ['type' => 'boolean', 'value' => null],
            'autoDelete' => ['type' => 'boolean', 'value' => null],
            'internal' => ['type' => 'boolean', 'value' => null],
            'bindings' => ['type' => 'array', 'value' => null],
            'arguments' => ['type' => 'array', 'value' => null],
        ];

        $bindingFields = [
            'id' => ['type' => 'string', 'value' => null],
            'destinationType' => ['type' => 'string', 'value' => ['queue']],
            'destination' => ['type' => 'string', 'value' => null],
            'disabled' => ['type' => 'boolean', 'value' => null],
            'topic' => ['type' => 'string', 'value' => null],
            'arguments' => ['type' => 'array', 'value' => null],
        ];

        $this->validateFields('exchange', $exchangeName, $exchangeConfig, $fields);

        $this->validateBindings($exchangeName, $exchangeConfig, $bindingFields);
    }

    /**
     * Validate binding config.
     *
     * @param string $exchangeName
     * @param array $exchangeConfig
     * @param array $bindingFields
     * @return void
     * @throws \LogicException
     */
    private function validateBindings($exchangeName, $exchangeConfig, $bindingFields)
    {
        foreach ($bindingFields as $bindFieldName => $bindExpectedType) {
            foreach ($exchangeConfig['bindings'] as $bindingConfig) {
                $actualType = gettype($bindingConfig[$bindFieldName]);
                if ($actualType !== $bindExpectedType['type']) {
                    throw new \LogicException(
                        sprintf(
                            "Type of '%s' field specified in configuration of '%s' exchange is invalid. "
                            . "Given '%s', '%s' was expected.",
                            $bindFieldName,
                            $exchangeName,
                            $actualType,
                            $bindExpectedType['type']
                        )
                    );
                }

                if ($bindExpectedType['value'] &&
                    !in_array($bindingConfig[$bindFieldName], $bindExpectedType['value'])
                ) {
                    throw new \LogicException(
                        sprintf(
                            "Value of '%s' field specified in configuration of '%s' exchange is invalid. "
                            . "Given '%s', '%s' was expected.",
                            $bindFieldName,
                            $exchangeName,
                            $bindingConfig[$bindFieldName],
                            implode(' or ', $bindExpectedType['value'])
                        )
                    );
                }
            }
        }
    }

    /**
     * Make sure types of all fields in the queue declaration are correct.
     *
     * @param string $queueName
     * @param array $queueConfig
     * @return void
     * @throws \LogicException
     */
    private function validateQueueFieldsTypes($queueName, $queueConfig)
    {
        $fields = [
            'name' => ['type' => 'string', 'value' => null],
            'type' => ['type' => 'string', 'value' => ['queue']],
            'connection' => ['type' => 'string', 'value' => null],
            'durable' => ['type' => 'boolean', 'value' => null],
            'autoDelete' => ['type' => 'boolean', 'value' => null],
            'arguments' => ['type' => 'array', 'value' => null],
        ];

        $this->validateFields('queue', $queueName, $queueConfig, $fields);
    }

    /**
     * Make sure configured fields match their expected types and allowed values.
     *
     * @param string $entityType
     * @param string $name
     * @param array $config
     * @param array $fields
     * @return void
     * @throws \LogicException
     */
    private function validateFields($entityType, $name, $config, $fields)
    {
        foreach ($fields as $fieldName => $expectedType) {
            $actualType = gettype($config[$fieldName]);
            if ($actualType !== $expectedType['type']) {
                throw new \LogicException(
                    sprintf(
                        "Type of '%s' field specified in configuration of '%s' %s is invalid. "
                        . "Given '%s', '%s' was expected.",
                        $fieldName,
                        $name,
                        $entityType,
                        $actualType,
                        $expectedType['type']
                    )
                );
            }

            if ($expectedType['value'] && !in_array($config[$fieldName], $expectedType['value'])) {
                throw new \LogicException(
                    sprintf(
                        "Value of '%s' field specified in configuration of '%s' %s is invalid. "
                        . "Given '%s', '%s' was expected.",
                        $fieldName,
                        $name,
                        $entityType,
                        $config[$fieldName],
                        implode(' or ', $expectedType['value'])
                    )
                );
            }
        }
    }
}
