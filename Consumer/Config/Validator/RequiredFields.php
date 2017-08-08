<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\ValidatorInterface;

/**
 * Consumer config data validator for required fields.
 * @since 2.2.0
 */
class RequiredFields implements ValidatorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function validate($configData)
    {
        foreach ($configData as $consumerName => $consumerConfig) {
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
}
