<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Validator\Attribute;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\AbstractValidator;
use Zend_Validate;

/**
 * Class Code
 *
 * Validation EAV attribute code
 */
class Code  extends AbstractValidator
{
    /**
     * Validates the correctness of the attribute code
     *
     * @param string $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isValid($attributeCode): bool
    {
        /**
         * Check attribute_code for allowed characters
         */
        if (trim($attributeCode)
            && !preg_match('/^[a-z][a-z0-9_]*$/', trim($attributeCode))
        ) {
            throw new LocalizedException(
                __(
                    'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                    'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                    $attributeCode
                )
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
            throw new LocalizedException(__(
                'An attribute code must not be less than %1 and more than %2 characters.',
                $minLength,
                $maxLength
            ));
        }

        return true;
    }
}
