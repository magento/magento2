<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Address;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Customer telephone fields validator.
 */
class Telephone extends AbstractValidator
{
    /**
     * Maximum allowed length for telephone value.
     */
    private const MAX_TELEPHONE_LENGTH = 255;

    /**
     * Allowed char:
     *
     * \() :Matches open and close parentheses
     * \+: Matches the plus sign.
     * \-: Matches the hyphen.
     * \.: Matches the dot character (e.g. 06.76.40.32.22)
     * \/: Matches the forward slash (e.g. +43680/2149568)
     * \d: Digits (0-9).
     * \s: Matches whitespace characters.
     */
    private const PATTERN_TELEPHONE_CHARSET = '/^[\d\s+().\/ -]+$/u';

    /**
     * Validate telephone fields.
     *
     * @param \Magento\Customer\Model\Address $value
     * @return bool
     */
    public function isValid($value): bool
    {
        $telephoneValue = (string)$value->getTelephone();
        if (!$this->isValidTelephoneLength($telephoneValue)) {
            parent::_addMessages([[
                'telephone' => __(
                    'Invalid Phone Number. The phone number is too long. Enter no more than %1 characters.',
                    self::MAX_TELEPHONE_LENGTH
                )
            ]]);
        } elseif (!$this->isValidTelephoneCharset($telephoneValue)) {
            parent::_addMessages([[
                'telephone' => "Invalid Phone Number. Please use 0-9, +, -, (, ), ., / and space."
            ]]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check if telephone field is valid.
     *
     * @param string|null $telephoneValue
     * @return bool
     */
    private function isValidTelephoneLength(?string $telephoneValue): bool
    {
        if ($telephoneValue !== null) {
            return mb_strlen($telephoneValue) <= self::MAX_TELEPHONE_LENGTH;
        }

        return true;
    }

    /**
     * Check if telephone field uses only allowed characters.
     *
     * @param string|null $telephoneValue
     * @return bool
     */
    private function isValidTelephoneCharset(?string $telephoneValue): bool
    {
        if ($telephoneValue !== null && $telephoneValue !== '') {
            return preg_match(self::PATTERN_TELEPHONE_CHARSET, $telephoneValue) === 1;
        }

        return true;
    }
}
