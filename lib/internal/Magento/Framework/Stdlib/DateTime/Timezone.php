<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Stdlib\DateTime;

/**
 * Timezone library
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
     * @var \Magento\Framework\App\ScopeResolverInterface
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $scopeType
     * @param string $defaultTimezonePath
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
     */
    public function getDefaultTimezonePath()
    {
        return $this->_defaultTimezonePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultTimezone()
    {
        return 'UTC';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTimezone()
    {
        return $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType);
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFormat($type = \IntlDateFormatter::SHORT)
    {
        return (new \IntlDateFormatter(
            $this->_localeResolver->getLocaleCode(),
            $type,
            \IntlDateFormatter::NONE
        ))->getPattern();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFormatWithLongYear()
    {
        return preg_replace(
            '/(?<!y)yy(?!y)/',
            'yyyy',
            $this->getDateFormat()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeFormat($type = \IntlDateFormatter::SHORT)
    {
        return (new \IntlDateFormatter(
            $this->_localeResolver->getLocaleCode(),
            \IntlDateFormatter::NONE,
            $type
        ))->getPattern();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateTimeFormat($type)
    {
        return $this->getDateFormat($type) . ' ' . $this->getTimeFormat($type);
    }

    /**
     * {@inheritdoc}
     */
    public function date($date = null, $part = null, $locale = null, $useTimezone = true)
    {
        $locale = $locale ? $locale : $this->_localeResolver->getLocale()->toString();
        $timezone = $useTimezone
            ? $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType)
            : 'UTC';

        if (empty($date)) {
            return new \DateTime('now', new \DateTimeZone($timezone));
        }
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, $timezone);
        return new \DateTime('@' . $formatter->parse($date));
    }

    /**
     * {@inheritdoc}
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
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false)
    {
        if ($showTime) {
            $format = $this->getDateTimeFormat($format);
        } else {
            $format = $this->getDateFormat($format);
        }

        if ($date instanceof \DateTime) {
            return $date->format($format);
        } else {
            return (new \DateTime($date))->format($format);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function formatTime($time = null, $format = \IntlDateFormatter::SHORT, $showDate = false)
    {
        if (!in_array($format, $this->_allowedFormats, true)) {
            return $time;
        }

        $date = $time;
        if (!($time instanceof \DateTimeInterface)) {
            $date = new \DateTime($time);
        }

        if ($showDate) {
            $format = $this->getDateTimeFormat($format);
        } else {
            $format = $this->getTimeFormat($format);
        }

        return $date->format($format);
    }

    /**
     * {@inheritdoc}
     */
    public function utcDate($scope, $date, $includeTime = false)
    {
        $dateObj = $this->scopeDate($scope, $date, $includeTime);
        $dateObj->setTimezone(new \DateTimeZone('UTC'));
        return $dateObj;
    }

    /**
     * {@inheritdoc}
     */
    public function scopeTimeStamp($scope = null)
    {
        $timezone = $this->_scopeConfig->getValue($this->getDefaultTimezonePath(), $this->_scopeType, $scope);
        return (new \DateTime('now', new \DateTimeZone($timezone)))->getTimestamp();
    }

    /**
     * {@inheritdoc}
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
     * Returns a localized information string, supported are several types of information.
     * For detailed information about the types look into the documentation
     *
     * @param string $value Name to get detailed information about
     * @param string $path (Optional) Type of information to return
     * @return string|false The wished information in the given language
     */
    protected function _getTranslation($value = null, $path = null)
    {
        return $this->_localeResolver->getLocale()->getTranslation($value, $path, $this->_localeResolver->getLocale());
    }

    /**
     * @param \DateTimeInterface $date
     * @param int $dateType
     * @param int $timeType
     * @param null $locale
     * @param null $timezone
     * @return mixed
     */
    public function formatDateTime(
        \DateTimeInterface $date,
        $dateType = \IntlDateFormatter::SHORT,
        $timeType = \IntlDateFormatter::SHORT,
        $locale = null,
        $timezone = null
    ) {
        $formatter = new \IntlDateFormatter(
            $locale ?: $this->_localeResolver->getLocaleCode(),
            $dateType,
            $timeType,
            $timezone ?: 'UTC'
        );
        return $formatter->format($date);
    }
}
