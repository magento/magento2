<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;

/**
 * Consumer config data validator for required fields.
 */
class RequiredFields implements ValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerName => $consumerConfig) {
            $requiredFields = [
                'name',
                'queue',
                'handlers',
                'consumerInstance',
                'connection',
                'maxMessages',
                'maxIdleTime',
                'sleep',
                'onlySpawnWhenMessageAvailable'
            ];
            foreach ($requiredFields as $fieldName) {
                if (!array_key_exists($fieldName, $consumerConfig)) {
                    throw new \LogicException(
                        sprintf("'%s' field must be specified for consumer '%s'", $fieldName, $consumerName)
                    );
                }
            }
        }
    }
}
