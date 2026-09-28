<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

/**
 * Composite validator for service input validation
 */
class CompositeServiceInputValidator implements ServiceInputValidatorInterface
{
    /**
     * @var array
     */
    private $validators;

    /**
     * @param ServiceInputValidatorInterface[] $validators
     */
    public function __construct(array $validators)
    {
        foreach ($validators as $validator) {
            if (!$validator instanceof ServiceInputValidatorInterface) {
                throw new \InvalidArgumentException(
                    "Validators must implement " . ServiceInputValidatorInterface::class
                );
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        foreach ($this->validators as $validator) {
            $validator->validateComplexArrayType($className, $items);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void
    {
        foreach ($this->validators as $validator) {
            $validator->validateEntityValue($entity, $propertyName, $value);
        }
    }
}
