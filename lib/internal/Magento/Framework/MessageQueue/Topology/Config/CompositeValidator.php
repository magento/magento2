<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Topology\Config;

/**
 * Topology config data validator.
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * Config validator list.
     *
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * Validator constructor.
     *
     * @param ValidatorInterface[] $validators
     */
    public function __construct($validators)
    {
        $this->validators = $validators;
    }

    /**
     * Validate merged topology config data.
     *
     * @param array $configData
     * @throws \LogicException
     * @return void
     * @throws \LogicException
     */
    public function validate($configData)
    {
        foreach ($this->validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new \LogicException(
                    sprintf(
                        'Validator [%s] does not implements %s',
                        ValidatorInterface::class,
                        get_class($validator)
                    )
                );
            }
            $validator->validate($configData);
        }
    }
}
