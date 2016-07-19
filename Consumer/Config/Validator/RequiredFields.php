<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;

/**
 * Consumer config data validator for required fields.
 */
class RequiredFields implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerName => $consumerConfig) {
            $this->validateConsumerRequiredFields($consumerName, $consumerConfig);
        }
    }

    /**
     * Make sure all required fields are present in the consumer item config.
     *
     * @param string $consumerName
     * @param array $consumerConfig
     * @return void
     * @throws \LogicException
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
}
