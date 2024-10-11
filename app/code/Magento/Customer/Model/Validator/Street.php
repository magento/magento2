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
 * Customer street fields validator.
 */
class Street extends AbstractValidator
{
    /**
     * Allowed characters:
     *
     * \p{L}: Unicode letters.
     * \p{M}: Unicode marks (diacritic marks, accents, etc.).
     * ,: Comma.
     * -: Hyphen.
     * .: Period.
     * `'’: Single quotes, both regular and right single quotation marks.
     * &: Ampersand.
     * \s: Whitespace characters (spaces, tabs, newlines, etc.).
     * \d: Digits (0-9).
     */
    private const PATTERN_STREET = "/(?:[\p{L}\p{M}\"[],-.'’`&\s\d]){1,255}+/u";

    /**
     * Validate street fields.
     *
     * @param Customer $customer
     * @return bool
     */
    public function isValid($customer)
    {
        foreach ($customer->getStreet() as $street) {
            if (!$this->isValidStreet($street)) {
                parent::_addMessages([[
                    'street' => "Invalid Street Address. Please use A-Z, a-z, 0-9, , - . ' ’ ` & spaces"
                ]]);
            }
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check if street field is valid.
     *
     * @param string|null $streetValue
     * @return bool
     */
    private function isValidStreet($streetValue)
    {
        if ($streetValue != null) {
            if (preg_match(self::PATTERN_STREET, $streetValue, $matches)) {
                return $matches[0] == $streetValue;
            }
        }

        return true;
    }
}
