<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Customer\Model\Customer;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Customer city fields validator.
 */
class City extends AbstractValidator
{
    /**
     * Allowed characters:
     *
     * \p{L}: Unicode letters.
     * \p{M}: Unicode marks (diacritic marks, accents, etc.).
     * ': Apostrophe mark.
     * \s: Whitespace characters (spaces, tabs, newlines, etc.).
     * \-: Hyphen.
     * \.: Period.
     * \&: Ampersand.
     * \[\]: Square brackets.
     * \(\): Parentheses.
     */
    private const PATTERN_CITY = '/^[\p{L}\p{M}\s\-\.\'\&\[\]\(\)]{1,100}$/u';

    /**
     * Validate city fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer): bool
    {
        if (!$this->isValidCity($customer->getCity())) {
            parent::_addMessages([[
                'city' => __("Invalid City. Please use only letters, spaces, hyphens, apostrophes, periods, ampersands, square brackets, and parentheses.")
            ]]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check if city field is valid.
     *
     * @param string|null $cityValue
     * @return bool
     */
    private function isValidCity(?string $cityValue): bool
    {
        if ($cityValue !== null) {
            if (preg_match(self::PATTERN_CITY, $cityValue, $matches)) {
                return $matches[0] === $cityValue;
            }
        }

        return true;
    }
}
