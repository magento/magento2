<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\Validator;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Validate with multiple validators
 */
class CompositeValidator implements ValidatorInterface
{
    /**
     * @var array
     */
    private $validators;

    /**
     * @param ValidatorInterface[] $validators
     * @throws GraphQlInputException
     */
    public function __construct(array $validators)
    {
        foreach ($validators as $validator) {
            if (!$validator instanceof ValidatorInterface) {
                throw new GraphQlInputException(__("Validators must implement " . ValidatorInterface::class));
            }
        }
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    public function validate(Field $field, $args): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($field, $args);
        }
    }
}
