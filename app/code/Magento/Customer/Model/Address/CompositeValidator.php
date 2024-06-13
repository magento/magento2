<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\Address;

/**
 * Address composite validator.
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    /**
     * @param array $validators
     */
    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * @inheritdoc
     */
    public function validate(AbstractAddress $address)
    {
        $errors = [];
        foreach ($this->validators as $validator) {
            $errors[] = $validator->validate($address);
        }

        return array_merge([], ...$errors);
    }
}
