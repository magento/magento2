<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Framework\Phrase;

/**
 * Customer telephone field validator.
 */
class Telephone extends AbstractAddressFieldValidator
{
    /** Matches customer_address_entity.telephone varchar(255) and quote_address.telephone varchar(255). */
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
     * @inheritdoc
     */
    public function getFieldKey(): string
    {
        return 'telephone';
    }

    /**
     * @inheritdoc
     */
    public function getMaxLength(): int
    {
        return self::MAX_TELEPHONE_LENGTH;
    }

    /**
     * @inheritdoc
     */
    public function getCharsetPattern(): string
    {
        return self::PATTERN_TELEPHONE_CHARSET;
    }

    /**
     * @inheritdoc
     */
    public function getLengthErrorMessage(): Phrase
    {
        return __(
            'Invalid Phone Number. The phone number is too long. Enter no more than %1 characters.',
            self::MAX_TELEPHONE_LENGTH
        );
    }

    /**
     * @inheritdoc
     */
    public function getCharsetErrorMessage(): Phrase
    {
        return __('Invalid Phone Number. Please use 0-9, +, -, (, ), ., / and space.');
    }

    /**
     * @inheritdoc
     */
    public function getFieldValues(mixed $value): array
    {
        return [(string)$value->getTelephone()];
    }
}
