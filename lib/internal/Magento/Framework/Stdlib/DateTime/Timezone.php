<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

use \Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Timezone library
 * @since 2.0.0
 */
class Timezone implements TimezoneInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_allowedFormats = [
        \IntlDateFormatter::FULL,
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::MEDIUM,
        \IntlDateFormatter::SHORT,
    ];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_scopeType;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     * @since 2.0.0
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     * @since 2.0.0
     */
    protected $_dateTime;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_defaultTimezonePath;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.1.0
     */
    protected $_localeResolver;

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $scopeType
     * @param string $defaultTimezonePath
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $scopeType,
        $defaultTimezonePath
    ) {
        $this->_scopeResolver = $scopeResolver;
        $this->_localeResolver = $localeResolver;
        $this->_dateTime = $dateTime;
        $this->_defaultTimezonePath = $defaultTimezonePath;
        $this->_scopeConfig = $scopeConfig;
        $this->_scopeType = $scopeType;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDefaultTimezonePath()
    {
        return $this->_defaultTimezonePath;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDefaultTimezone()
    {
        return 'UTC';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDateFormat($type = \IntlDateFormatter::SHORT)
    {
        return (new \IntlDateFormatter(
            $this->_localeResolver->getLocale(),
            $type,
            \IntlDateFormatter::NONE
        ))->getPattern();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDateFormatWithLongYear()
    {
        return preg_replace(
            '/(?<!y)yy(?!y)/',
            'Y',
            $this->getDateFormat()
        );
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTimeFormat($type = \IntlDateFormatter::SHORT)
    {
        return (new \IntlDateFormatter(
            $this->_localeResolver->getLocale(),
            \IntlDateFormatter::NONE,
            $type
        ))->getPattern();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDateTimeFormat($type)
    {
        return $this->getDateFormat($type) . ' ' . $this->getTimeFormat($type);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function date($date = null, $locale = null, $useTimezone = true, $includeTime = true)
    {
        $locale = $locale ?: $this->_localeResolver->getLocale();
        $timezone = $useTimezone
            ? $this->getConfigTimezone()
            : date_default_timezone_get();

        switch (true) {
            case (empty($date)):
                return new \DateTime('now', new \DateTimeZone($timezone));
            case ($date instanceof \DateTime):
                return $date->setTimezone(new \DateTimeZone($timezone));
            case ($date instanceof \DateTimeImmutable):
                return new \DateTime($date->format('Y-m-d H:i:s'), $date->getTimezone());
            case (!is_numeric($date)):
                $timeType = $includeTime ? \IntlDateFormatter::SHORT : \IntlDateFormatter::NONE;
                $formatter = new \IntlDateFormatter(
                    $locale,
                    \IntlDateFormatter::SHORT,
                    $timeType,
                    new \DateTimeZone($timezone)
                );
                $date = $formatter->parse($date) ?: (new \DateTime($date))->getTimestamp();
                break;
        }

        return (new \DateTime(null, new \DateTimeZone($timezone)))->setTimestamp($date);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function scopeDate($scope = null, $date = null, $includeTime = false)
    {
        $timezone = $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType, $scope);
        $date = new \DateTime(is_numeric($date) ? '@' . $date : $date, new \DateTimeZone($timezone));
        if (!$includeTime) {
            $date->setTime(0, 0, 0);
        }
        return $date;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false)
    {
        $formatTime = $showTime ? $format : \IntlDateFormatter::NONE;

        if (!($date instanceof \DateTimeInterface)) {
            $date = new \DateTime($date);
        }

        return $this->formatDateTime($date, $format, $formatTime);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isScopeDateInInterval($scope, $dateFrom = null, $dateTo = null)
    {
        if (!$scope instanceof \Magento\Framework\App\ScopeInterface) {
            $scope = $this->_scopeResolver->getScope($scope);
        }

        $scopeTimeStamp = $this->scopeTimeStamp($scope);
        $fromTimeStamp = strtotime($dateFrom);
        $toTimeStamp = strtotime($dateTo);
        if ($dateTo) {
            // fix date YYYY-MM-DD 00:00:00 to YYYY-MM-DD 23:59:59
            $toTimeStamp += 86400;
        }

        $result = false;
        if (!$this->_dateTime->isEmptyDate($dateFrom) && $scopeTimeStamp < $fromTimeStamp) {
        } elseif (!$this->_dateTime->isEmptyDate($dateTo) && $scopeTimeStamp > $toTimeStamp) {
        } else {
            $result = true;
        }
        return $result;
    }

    /**
     * @param string|\DateTimeInterface $date
     * @param int $dateType
     * @param int $timeType
     * @param null $locale
     * @param null $timezone
     * @param string|null $pattern
     * @return string
     * @since 2.0.0
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

        $formatter = new \IntlDateFormatter(
            $locale ?: $this->_localeResolver->getLocale(),
            $dateType,
            $timeType,
            $timezone,
            null,
            $pattern
        );
        return $formatter->format($date);
    }

    /**
     * Convert date from config timezone to Utc.
     * If pass \DateTime object as argument be sure that timezone is the same with config timezone
     *
     * @param string|\DateTimeInterface $date
     * @param string $format
     * @throws LocalizedException
     * @return string
     * @since 2.1.0
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
                    new Phrase('DateTime object timezone must be the same as config - %1', $this->getConfigTimezone())
                );
            }
        }

        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format($format);
    }
}
