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
use Zend_Validate;
use Zend_Validate_Exception;

class Code extends AbstractValidator
{
    /**
     * Validation pattern for attribute code
     */
    const VALIDATION_RULE_PATTERN = '/^[a-zA-Z]+[a-zA-Z0-9_]*$/u';

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param string $attributeCode
     * @return boolean
     * @throws Zend_Validate_Exception If validation of $attributeCode is impossible
     */
    public function isValid($attributeCode): bool
    {
        $errorMessages = [];
        /**
         * Check attribute_code for allowed characters
         */
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
        $isAllowedLength = Zend_Validate::is(
            trim($attributeCode),
            'StringLength',
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
