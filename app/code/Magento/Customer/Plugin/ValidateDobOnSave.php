<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Customer\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Locale\ResolverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateDobOnSave
{
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var JsonSerializer
     */
    private $json;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @param EavConfig $eavConfig
     * @param JsonSerializer $json
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        EavConfig $eavConfig,
        JsonSerializer $json,
        ResolverInterface $localeResolver
    ) {
        $this->eavConfig = $eavConfig;
        $this->json = $json;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Enforce DOB min/max from attribute validate_rules on every save.
     *
     * @param CustomerRepositoryInterface $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return mixed
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        CustomerRepositoryInterface $subject,
        callable $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        $dobRaw = $customer->getDob();

        $dobDate = $this->parseDate($dobRaw);
        if ($dobRaw !== null && $dobRaw !== '' && !$dobDate) {
            throw new InputException(__('Date of Birth is invalid.'));
        }

        if ($dobDate) {
            $normalizedDob = $dobDate->format(DateTime::DATE_PHP_FORMAT);
            $customer->setDob($normalizedDob);
            $attr = $this->eavConfig->getAttribute('customer', 'dob');
            $rules = $attr->getData('validate_rules');
            if (is_string($rules) && $rules !== '') {
                try {
                    $rules = $this->json->unserialize($rules);
                } catch (\InvalidArgumentException $e) {
                    $rules = [];
                }
            }
            if (!is_array($rules)) {
                $rules = (array)$attr->getValidateRules();
            }

            $min = $rules['date_range_min'] ?? $rules['min_date'] ?? null;
            $max = $rules['date_range_max'] ?? $rules['max_date'] ?? null;

            $minDate = $this->parseDate($min);
            $maxDate = $this->parseDate($max);

            $dobKey = $dobDate->format(DateTime::DATE_PHP_FORMAT);

            if ($minDate && $dobKey < $minDate->format(DateTime::DATE_PHP_FORMAT)) {
                throw new InputException(__(
                    'Date of Birth must be on or after %1.',
                    $minDate->format(DateTime::DATE_PHP_FORMAT)
                ));
            }
            if ($maxDate && $dobKey > $maxDate->format(DateTime::DATE_PHP_FORMAT)) {
                throw new InputException(__(
                    'Date of Birth must be on or before %1.',
                    $maxDate->format(DateTime::DATE_PHP_FORMAT)
                ));
            }
        }

        return $proceed($customer, $passwordHash);
    }

    /**
     * Parse a date value into DateTimeImmutable.
     *
     * @param mixed $value
     * @return \DateTimeImmutable|null
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parseDate($value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '' || $value === false) {
            return null;
        }
        if (is_int($value) || (is_string($value) && ctype_digit($value))) {
            $intVal = (int)$value;
            if ($intVal <= 0) {
                return null;
            }
            $seconds = ($intVal >= 10000000000) ? intdiv($intVal, 1000) : $intVal;
            return (new \DateTimeImmutable('@' . $seconds))->setTimezone(new \DateTimeZone('UTC'));
        }
        $stringValue = (string)$value;
        $locale = $this->localeResolver->getLocale();
        $formatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );
        $formatter->setPattern(DateTime::DATE_INTERNAL_FORMAT);
        $timestamp = $formatter->parse($stringValue);
        if ($timestamp !== false) {
            return new \DateTimeImmutable('@' . $timestamp);
        }

        try {
            return new \DateTimeImmutable($stringValue);
        } catch (\Exception $e) {
            return null;
        }
    }
}
