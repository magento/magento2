<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Framework\Phrase;

/**
 * Customer street field validator.
 */
class Street extends AbstractAddressFieldValidator
{
    /** Constrained by quote_address.street varchar(255), the narrowest column across all address tables. */
    private const MAX_STREET_LENGTH = 255;

    /** Allows Unicode letters/marks, digits, common address punctuation (.,#&/-), quotes (' ' '), parentheses, and whitespace (space/tab/newline). */
    private const PATTERN_STREET_CHARSET = "/^[\p{L}\p{M}\d \t\n\-'\u{2018}\u{2019}\.,&#\/\(\)]*\$/u";

    /**
     * @inheritdoc
     */
    public function getFieldKey(): string
    {
        return 'street';
    }

    /**
     * @inheritdoc
     */
    public function getMaxLength(): int
    {
        return self::MAX_STREET_LENGTH;
    }

    /**
     * @inheritdoc
     */
    public function getCharsetPattern(): string
    {
        return self::PATTERN_STREET_CHARSET;
    }

    /**
     * @inheritdoc
     */
    public function getLengthErrorMessage(): Phrase
    {
        return __(
            'Invalid Street Address. The street address is too long. Enter no more than %1 characters.',
            self::MAX_STREET_LENGTH
        );
    }

    /**
     * @inheritdoc
     */
    public function getCharsetErrorMessage(): Phrase
    {
        return __('Invalid Street Address. The street address contains invalid characters.');
    }

    /**
     * @inheritdoc
     */
    public function getFieldValues(mixed $value): array
    {
        $street = $value->getStreet() ?? [];
        if (count($street) === 1) {
            $decoded = json_decode($street[0], true);
            if (is_array($decoded)) {
                $street = $decoded;
            }
        }
        return [implode("\n", $street)];
    }
}
