<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Customer\Attribute;

use Magento\Customer\Model\Customer\Attribute\ValidatorInterface;
use Magento\Framework\Api\AttributeInterface;

/**
 * Customer custom attribute validator.
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
    public function validate(AttributeInterface $customAttribute): void
    {
        foreach ($this->validators as $validator) {
            $validator->validate($customAttribute);
        }
    }
}
