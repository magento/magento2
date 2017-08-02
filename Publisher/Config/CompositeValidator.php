<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Publisher config data validator.
 * @since 2.2.0
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * Config validator list.
     *
     * @var ValidatorInterface[]
     * @since 2.2.0
     */
    private $validators;

    /**
     * Validator constructor.
     *
     * @param ValidatorInterface[] $validators
     * @since 2.2.0
     */
    public function __construct($validators)
    {
        $this->validators = $validators;
    }

    /**
     * Validate merged publisher config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     * @throws \LogicException
     * @since 2.2.0
     */
    public function validate($configData)
    {
        foreach ($this->validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new \LogicException(
                    sprintf(
                        'Validator [%s] does not implements ' .
                        'Magento\Framework\MessageQueue\Publisher\Config\ValidatorInterface',
                        get_class($validator)
                    )
                );
            }
            $validator->validate($configData);
        }
    }
}
