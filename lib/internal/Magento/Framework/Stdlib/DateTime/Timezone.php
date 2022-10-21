<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\DateTime;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\Intl\DateFormatterFactory;

/**
 * Timezone library
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Timezone implements TimezoneInterface
{
    /**
     * @var array
     */
    protected $_allowedFormats = [
        \IntlDateFormatter::FULL,
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::MEDIUM,
        \IntlDateFormatter::SHORT,
    ];

    /**
     * @var string
     */
    protected $_scopeType;

    /**
     * @var ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * @var string
     */
    protected $_defaultTimezonePath;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var DateFormatterFactory
     */
    private $dateFormatterFactory;

    /**
     * @param ScopeResolverInterface $scopeResolver
     * @param ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param string $scopeType
     * @param string $defaultTimezonePath
     * @param DateFormatterFactory $dateFormatterFactory
     */
    public function __construct(
        ScopeResolverInterface $scopeResolver,
        ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        $scopeType,
        $defaultTimezonePath,
        DateFormatterFactory $dateFormatterFactory
    ) {
        $this->_scopeResolver = $scopeResolver;
        $this->_localeResolver = $localeResolver;
        $this->_dateTime = $dateTime;
        $this->_defaultTimezonePath = $defaultTimezonePath;
        $this->_scopeConfig = $scopeConfig;
        $this->_scopeType = $scopeType;
        $this->dateFormatterFactory = $dateFormatterFactory;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTimezonePath()
    {
        return $this->_defaultTimezonePath;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultTimezone()
    {
        return 'UTC';
    }

    /**
     * @inheritdoc
     */
    public function getConfigTimezone($scopeType = null, $scopeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $this->getDefaultTimezonePath(),
            $scopeType ?: $this->_scopeType,
            $scopeCode
        );
    }

    /**
     * @inheritdoc
     */
    public function getDateFormat($type = \IntlDateFormatter::SHORT)
    {
        $formatter = $this->dateFormatterFactory->create(
            (string)$this->_localeResolver->getLocale(),
            (int)$type,
            \IntlDateFormatter::NONE,
            null,
            false
        );

        return $formatter->getPattern();
    }

    /**
     * @inheritdoc
     */
    public function getDateFormatWithLongYear()
    {
        $formatter = $this->dateFormatterFactory->create(
            (string)$this->_localeResolver->getLocale(),
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE
        );

        return $formatter->getPattern();
    }

    /**
     * @inheritdoc
     */
    public function getTimeFormat($type = \IntlDateFormatter::SHORT)
    {
        $formatter = $this->dateFormatterFactory->create(
            (string)$this->_localeResolver->getLocale(),
            \IntlDateFormatter::NONE,
            (int)$type
        );

        return $formatter->getPattern();
    }

    /**
     * @inheritdoc
     */
    public function getDateTimeFormat($type)
    {
        return $this->getDateFormat($type) . ' ' . $this->getTimeFormat($type);
    }

    /**
     * @inheritdoc
     */
    public function date($date = null, $locale = null, $useTimezone = true, $includeTime = true)
    {
        $locale = (string)($locale ?: $this->_localeResolver->getLocale());
        $timezone = (string)($useTimezone ? $this->getConfigTimezone() : date_default_timezone_get());

        switch (true) {
            case (empty($date)):
                return new \DateTime('now', new \DateTimeZone($timezone));
            case ($date instanceof \DateTime):
                return $date->setTimezone(new \DateTimeZone($timezone));
            case ($date instanceof \DateTimeImmutable):
                return new \DateTime($date->format('Y-m-d H:i:s'), $date->getTimezone());
            case (!is_numeric($date)):
                $date = $this->appendTimeIfNeeded((string)$date, (bool)$includeTime, $timezone, $locale);
                $formatter = $this->dateFormatterFactory->create(
                    $locale,
                    \IntlDateFormatter::SHORT,
                    $includeTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE,
                    $timezone
                );
                $date = $formatter->parse($date) ?: (new \DateTime($date))->getTimestamp();
                break;
        }

        return (new \DateTime('now', new \DateTimeZone($timezone)))->setTimestamp($date);
    }

    /**
     * @inheritdoc
     */
    public function scopeDate($scope = null, $date = null, $includeTime = false)
    {
        $timezone = new \DateTimeZone(
            $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType, $scope)
        );
        switch (true) {
            case (empty($date)):
                $date = new \DateTime('now', $timezone);
                break;
            case ($date instanceof \DateTime):
            case ($date instanceof \DateTimeImmutable):
                $date = $date->setTimezone($timezone);
                break;
            default:
                $date = new \DateTime(is_numeric($date) ? '@' . $date : $date);
                $date->setTimezone($timezone);
                break;
        }

        if (!$includeTime) {
            $date->setTime(0, 0, 0);
        }

        return $date;
    }

    /**
     * @inheritdoc
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false)
    {
        $formatTime = $showTime ? $format : \IntlDateFormatter::NONE;

        if (!$date instanceof \DateTimeInterface) {
            /** @phpstan-ignore-next-line */
            $date = new \DateTime($date ?? 'now');
        }

        return $this->formatDateTime($date, $format, $formatTime);
    }

    /**
     * @inheritdoc
     */
    public function scopeTimeStamp($scope = null)
    {
        $timezone = $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType, $scope);
        $currentTimezone = @date_default_timezone_get();
        @date_default_timezone_set($timezone);
        $date = date('Y-m-d H:i:s');
        @date_default_timezone_set($currentTimezone);
        return strtotime($date);
    }

    /**
     * @inheritdoc
     */
    public function isScopeDateInInterval($scope, $dateFrom = null, $dateTo = null)
    {
        $dateFrom = $dateFrom ?? '';
        $dateTo = $dateTo ?? '';
        if (!$scope instanceof ScopeInterface) {
            $scope = $this->_scopeResolver->getScope($scope);
        }

        $scopeTimeStamp = $this->scopeTimeStamp($scope);
        $fromTimeStamp = strtotime($dateFrom);
        $toTimeStamp = strtotime($dateTo);
        if ($dateTo) {
            // fix date YYYY-MM-DD 00:00:00 to YYYY-MM-DD 23:59:59
            $toTimeStamp += 86400;
        }

        return !(!$this->_dateTime->isEmptyDate($dateFrom) && $scopeTimeStamp < $fromTimeStamp ||
               !$this->_dateTime->isEmptyDate($dateTo) && $scopeTimeStamp > $toTimeStamp);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function formatDateTime(
        $date,
        $dateType = \IntlDateFormatter::SHORT,
        $timeType = \IntlDateFormatter::SHORT,
        $locale = null,
        $timezone = null,
        $pattern = null
    ) {
        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }
        if ($timezone === null) {
            if ($date->getTimezone() == null || $date->getTimezone()->getName() == 'UTC'
                || $date->getTimezone()->getName() == '+00:00'
            ) {
                $timezone = $this->getConfigTimezone();
            } else {
                $timezone = $date->getTimezone();
            }
        }

        $formatter = $this->dateFormatterFactory->create(
            (string) ($locale ?: $this->_localeResolver->getLocale()),
            // @phpstan-ignore-next-line
            (int) ($dateType ?? \IntlDateFormatter::SHORT),
            // @phpstan-ignore-next-line
            (int) ($timeType ?? \IntlDateFormatter::SHORT),
            null,
            false
        );
        if ($timezone) {
            $formatter->setTimeZone($timezone);
        }
        if ($pattern) {
            $formatter->setPattern($pattern);
        }

        return $formatter->format($date);
    }

    /**
     * @inheritdoc
     */
    public function convertConfigTimeToUtc($date, $format = 'Y-m-d H:i:s')
    {
        if (!($date instanceof \DateTimeInterface)) {
            if ($date instanceof \DateTimeImmutable) {
                $date = new \DateTime($date->format('Y-m-d H:i:s'), new \DateTimeZone($this->getConfigTimezone()));
            } else {
                $date = new \DateTime($date, new \DateTimeZone($this->getConfigTimezone()));
            }
        } else {
            if ($date->getTimezone()->getName() !== $this->getConfigTimezone()) {
                throw new LocalizedException(
                    new Phrase(
                        'The DateTime object timezone needs to be the same as the "%1" timezone in config.',
                        [$this->getConfigTimezone()]
                    )
                );
            }
        }

        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format($format);
    }

    /**
     * Append time to DateTime
     *
     * @param string $date
     * @param boolean $includeTime
     * @param string $timezone
     * @param string $locale
     * @return string
     * @throws LocalizedException
     */
    private function appendTimeIfNeeded(string $date, bool $includeTime, string $timezone, string $locale)
    {
        if ($includeTime && !preg_match('/\d{1}:\d{2}/', $date)) {
            $formatter = $this->dateFormatterFactory->create(
                $locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE,
                $timezone
            );
            $timestamp = $formatter->parse($date);
            if (!$timestamp) {
                throw new LocalizedException(
                    new Phrase(
                        'Could not append time to DateTime'
                    )
                );
            }

            $formatterWithHour = $this->dateFormatterFactory->create(
                $locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                $timezone
            );
            $date = $formatterWithHour->format($timestamp);
        }

        return $date;
    }
}
