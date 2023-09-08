<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\StringLength;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;

/**
 * Class Code
 *
 * Validation EAV attribute code
 */
class Code extends AbstractValidator
{
    /**
     * Validation pattern for attribute code
     */
    public const VALIDATION_RULE_PATTERN = '/^[a-zA-Z]+[a-zA-Z0-9_]*$/u';

    /**
     * Validates the correctness of the attribute code
     *
     * @param string $attributeCode
     * @return bool
     * @throws ValidateException
     */
    public function isValid($attributeCode): bool
    {
        $errorMessages = [];
        /**
         * Check attribute_code for allowed characters
         */
        $attributeCode = $attributeCode === null ? '' : $attributeCode;
        if (trim($attributeCode)
            && !preg_match(self::VALIDATION_RULE_PATTERN, trim($attributeCode))
        ) {
            $errorMessages[] = __(
                'Attribute code "%1" is invalid. Please use only letters (a-z or A-Z), ' .
                'numbers (0-9) or underscore (_) in this field, and the first character should be a letter.',
                $attributeCode
            );
        }

        /**
         * Check attribute_code for allowed length
         */
        $minLength = Attribute::ATTRIBUTE_CODE_MIN_LENGTH;
        $maxLength = Attribute::ATTRIBUTE_CODE_MAX_LENGTH;
        $isAllowedLength = ValidatorChain::is(
            trim($attributeCode),
            StringLength::class,
            ['min' => $minLength, 'max' => $maxLength]
        );
        if (!$isAllowedLength) {
            $errorMessages[] = __(
                'An attribute code must not be less than %1 and more than %2 characters.',
                $minLength,
                $maxLength
            );
        }

        /**
         * Check attribute_code for prohibited prefix
         */
        if (strpos($attributeCode, AbstractModifier::CONTAINER_PREFIX) === 0) {
            $errorMessages[] = __(
                '"%1" prefix is reserved by the system and cannot be used in attribute code names.',
                AbstractModifier::CONTAINER_PREFIX
            );
        }

        $this->_addMessages($errorMessages);

        return !$this->hasMessages();
    }
}
