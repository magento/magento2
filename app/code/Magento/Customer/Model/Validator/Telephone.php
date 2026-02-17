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
     * Allowed char:
     *
     * \() :Matches open and close parentheses
     * \+: Matches the plus sign.
     * \-: Matches the hyphen.
     * \d: Digits (0-9).
     * \s: Matches whitespace characters.
     */
    private const PATTERN_TELEPHONE = '/^[\d\s\+\-\()]{1,20}$/u';

    /**
     * Validate telephone fields.
     *
     * @param \Magento\Customer\Model\Address $value
     * @return bool
     */
    public function isValid($value): bool
    {
        if (!$this->isValidTelephone((string)$value->getTelephone())) {
            parent::_addMessages([[
                'telephone' => "Invalid Phone Number. Please use 0-9, +, -, (, ) and space."
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
    private function isValidTelephone(?string $telephoneValue): bool
    {
        if ($telephoneValue != null) {
            return preg_match(self::PATTERN_TELEPHONE, $telephoneValue) === 1;
        }

        return true;
    }
}
