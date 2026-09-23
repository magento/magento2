<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Framework\Phrase;

/**
 * Customer city field validator.
 */
class City extends AbstractAddressFieldValidator
{
    /** Matches customer_address_entity.city varchar(255) and quote_address.city varchar(255). */
    private const MAX_CITY_LENGTH = 255;

    /**
     * Allowed characters:
     *
     * \p{L}: Unicode letters.
     * \p{M}: Unicode marks (diacritic marks, accents, etc.).
     * \d: Digits (0-9).
     * \s: Whitespace characters (spaces, tabs, newlines, etc.).
     * -: Hyphen.
     * _: Underscore.
     * ', ’: Apostrophes (straight and typographical).
     * .: Period/full stop.
     * ,: Comma.
     * &: Ampersand.
     * (): Parentheses.
     */
    private const PATTERN_CITY_CHARSET = "/^[\p{L}\p{M}\d\s\-_'\u{2019}\.,&\(\)]+\$/u";

    /**
     * @inheritdoc
     */
    public function getFieldKey(): string
    {
        return 'city';
    }

    /**
     * @inheritdoc
     */
    public function getMaxLength(): int
    {
        return self::MAX_CITY_LENGTH;
    }

    /**
     * @inheritdoc
     */
    public function getCharsetPattern(): string
    {
        return self::PATTERN_CITY_CHARSET;
    }

    /**
     * @inheritdoc
     */
    public function getLengthErrorMessage(): Phrase
    {
        return __(
            'Invalid City. The city name is too long. Enter no more than %1 characters.',
            self::MAX_CITY_LENGTH
        );
    }

    /**
     * @inheritdoc
     */
    public function getCharsetErrorMessage(): Phrase
    {
        return __(
            "Invalid City. Please use letters, numbers, spaces, and the following characters: - _ ' \u{2019} . , & ( )"
        );
    }

    /**
     * @inheritdoc
     */
    public function getFieldValues(mixed $value): array
    {
        return [(string)$value->getCity()];
    }
}
