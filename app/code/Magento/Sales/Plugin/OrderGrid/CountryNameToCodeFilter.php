<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\OrderGrid;

use Magento\Framework\Locale\ListsInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class CountryNameToCodeFilter
{
    /**
     * @var ListsInterface
     */
    private ListsInterface $localeLists;

    /** @var array<string,string>|null */
    private ?array $nameToCode = null;

    /**
     * @param ListsInterface $localeLists
     */
    public function __construct(ListsInterface $localeLists)
    {
        $this->localeLists = $localeLists;
    }

    /**
     * Converts country names to ISO2 codes for billing/shipping address
     *
     * @param Collection $subject
     * @param callable $proceed
     * @param string $field
     * @param mixed $condition
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddFieldToFilter(
        Collection $subject,
        callable $proceed,
        $field,
        $condition = null
    ) {
        if (!in_array($field, ['billing_address', 'shipping_address'], true)) {
            return $proceed($field, $condition);
        }

        $condition = $this->normalizeCondition($condition);
        return $proceed($field, $condition);
    }

    /**
     * Normalizes Magento filter condition
     *
     * @param mixed $condition
     * @return array|mixed|string
     */
    private function normalizeCondition($condition)
    {
        if (is_string($condition)) {
            return $this->replaceCountryNameWithCode($condition);
        }

        if (!is_array($condition)) {
            return $condition;
        }

        foreach ($condition as $op => $value) {
            if (is_string($value)) {
                $condition[$op] = $this->replaceCountryNameWithCode($value);
            }
        }

        return $condition;
    }

    /**
     * Replaces a country name with its ISO2 country code
     *
     * @param string $input
     * @return string
     */
    private function replaceCountryNameWithCode(string $input): string
    {
        $trimForMatch = trim($input);
        $trimForMatchNoWildcards = trim($trimForMatch, " \t\n\r\0\x0B%");

        // Case 1: user typed only country name like "India" (or "%India%")
        $code = $this->resolveCountryCode($trimForMatchNoWildcards);
        if ($code) {
            return $this->replaceLastOccurrence($input, $trimForMatchNoWildcards, $code);
        }

        // Case 2: user typed address and last token is country name: "..., India"
        $parts = array_map('trim', explode(',', $trimForMatchNoWildcards));
        $last = trim((string)end($parts));
        if ($last !== '') {
            $code = $this->resolveCountryCode($last);
            if ($code) {
                return $this->replaceLastOccurrence($input, $last, $code);
            }
        }

        return $input;
    }

    /**
     * Resolves an input country name or ISO2 code
     *
     * @param string $nameOrCode
     * @return string|null
     */
    private function resolveCountryCode(string $nameOrCode): ?string
    {
        $value = trim($nameOrCode);
        if ($value === '') {
            return null;
        }

        // Already ISO2 code
        if (preg_match('/^[A-Za-z]{2}$/', $value)) {
            return strtoupper($value);
        }

        $map = $this->getNameToCodeMap();
        $key = mb_strtolower($value);
        return $map[$key] ?? null;
    }

    /**
     * Builds and caches a lowercase country-name
     *
     * @return array|string[]
     */
    private function getNameToCodeMap(): array
    {
        if ($this->nameToCode !== null) {
            return $this->nameToCode;
        }

        $map = [];
        foreach ($this->localeLists->getOptionCountries() as $row) {
            $code = isset($row['value']) ? (string)$row['value'] : '';
            $name = isset($row['label']) ? (string)$row['label'] : '';
            if ($code !== '' && $name !== '') {
                $map[mb_strtolower($name)] = strtoupper($code);
            }
        }

        $this->nameToCode = $map;
        return $map;
    }

    /**
     * Safely replaces only the last occurrence of a matched country name in the input string.
     *
     * @param string $haystack
     * @param string $needle
     * @param string $replacement
     * @return string
     */
    private function replaceLastOccurrence(string $haystack, string $needle, string $replacement): string
    {
        $pos = mb_strripos($haystack, $needle);
        if ($pos === false) {
            return $haystack;
        }
        return mb_substr($haystack, 0, $pos) . $replacement . mb_substr($haystack, $pos + mb_strlen($needle));
    }
}
