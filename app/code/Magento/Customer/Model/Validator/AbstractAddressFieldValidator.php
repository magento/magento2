<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Validator;

use Magento\Framework\Phrase;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Abstract validator for customer address string fields. Checks length first, then charset.
 */
abstract class AbstractAddressFieldValidator extends AbstractValidator
{
    /**
     * Returns the field key used in validation messages (e.g. 'telephone', 'city').
     *
     * @return string
     */
    abstract public function getFieldKey(): string;

    /**
     * Returns the maximum number of characters allowed; must not exceed the narrowest DB column size.
     *
     * @return int
     */
    abstract public function getMaxLength(): int;

    /**
     * Returns the anchored regex pattern (starting with ^ and ending with $) for allowed characters.
     *
     * @return string
     */
    abstract public function getCharsetPattern(): string;

    /**
     * Returns the error message shown when the field value exceeds the maximum length.
     *
     * @return Phrase
     */
    abstract public function getLengthErrorMessage(): Phrase;

    /**
     * Returns the error message shown when the field value contains invalid characters.
     *
     * @return Phrase
     */
    abstract public function getCharsetErrorMessage(): Phrase;

    /**
     * Extracts the list of string values to validate from the address model.
     *
     * @param mixed $value
     * @return array
     */
    abstract public function getFieldValues(mixed $value): array;

    /**
     * Validates address field(s); length is checked before charset to surface the most actionable error.
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value): bool
    {
        $this->_clearMessages();
        foreach ($this->getFieldValues($value) as $fieldValue) {
            $fieldValue = (string)$fieldValue;
            if (!$this->isValidLength($fieldValue)) {
                $this->_addMessages([[$this->getFieldKey() => $this->getLengthErrorMessage()]]);
            } elseif (!$this->isValidCharset($fieldValue)) {
                $this->_addMessages([[$this->getFieldKey() => $this->getCharsetErrorMessage()]]);
            }
        }

        return count($this->_messages) == 0;
    }

    /**
     * Check that the value does not exceed the maximum allowed length.
     *
     * @param string $value
     * @return bool
     */
    private function isValidLength(string $value): bool
    {
        return mb_strlen($value) <= $this->getMaxLength();
    }

    /**
     * Check that the value contains only allowed characters; empty strings are considered valid.
     *
     * @param string $value
     * @return bool
     */
    private function isValidCharset(string $value): bool
    {
        return $value === '' || preg_match($this->getCharsetPattern(), $value) === 1;
    }
}
